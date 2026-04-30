(function() {
    // Configuration
    const CONFIG = {
        apiEndpoint: "https://api.openai.com/v1/chat/completions",
        embeddingEndpoint: "https://api.openai.com/v1/embeddings",
        personaFile: "persona_details.json",
        emotionFile: "emotional_matrix.txt",
        searchBioFile: "searchBio.php",
        saveMemoryFile: "saveMemory.php",
        getMemoryFile: "getMemory.php",
        model: "gpt-4o-mini", // Using a standard model for the template
        embeddingModel: "text-embedding-3-small",
        embeddingDimensions: 512,
        kbSimilarityThreshold: 0.5
    };

    let chatMemory = [];
    let personaData = null;
    let emotionalMatrix = "";
    let isOpen = false;

    // Load styles
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = "widget.css";
    document.head.appendChild(link);

    // Create Widget HTML
    const container = document.createElement("div");
    container.className = "skynet-widget-container";
    container.innerHTML = `
        <div class="skynet-widget-trigger" id="skynet-trigger">
            <span style="font-size: 30px;">🤖</span>
        </div>
        <div class="skynet-chat-window" id="skynet-chat-window">
            <div class="skynet-chat-header">
                <h3>Skynet Expert: Marco Rossi</h3>
                <span class="skynet-close-btn" id="skynet-close">&times;</span>
            </div>
            <div class="skynet-chat-history" id="skynet-history">
                <div class="skynet-message bot">Hello! I am Marco Rossi, an expert here at Skynet. How can I assist you today with AI, defense, or robotics?</div>
            </div>
            <div id="skynet-loading" class="skynet-loading" style="padding: 0 12px;">Marco is thinking...</div>
            <div class="skynet-chat-input-area">
                <textarea class="skynet-chat-input" id="skynet-input" placeholder="Type a message..." rows="1"></textarea>
                <button class="skynet-send-btn" id="skynet-send">Send</button>
            </div>
        </div>
    `;
    document.body.appendChild(container);

    const trigger = document.getElementById("skynet-trigger");
    const chatWindow = document.getElementById("skynet-chat-window");
    const closeBtn = document.getElementById("skynet-close");
    const input = document.getElementById("skynet-input");
    const sendBtn = document.getElementById("skynet-send");
    const history = document.getElementById("skynet-history");
    const loading = document.getElementById("skynet-loading");

    // Toggle window
    trigger.onclick = () => {
        isOpen = !isOpen;
        chatWindow.classList.toggle("open", isOpen);
        if (isOpen && !personaData) {
            initPersona();
        }
    };

    closeBtn.onclick = () => {
        isOpen = false;
        chatWindow.classList.remove("open");
    };

    // Handle Send
    sendBtn.onclick = sendMessage;
    input.onkeydown = (e) => {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    };

    async function sendMessage() {
        const text = input.value.trim();
        if (!text) return;

        const apiKey = localStorage.getItem("openaikey");
        if (!apiKey) {
            const key = prompt("Please enter your OpenAI API Key:");
            if (key) {
                localStorage.setItem("openaikey", key);
            } else {
                return;
            }
        }

        addMessage("user", text);
        input.value = "";
        loading.style.display = "block";

        try {
            const response = await getAIResponse(text);
            addMessage("bot", response);
        } catch (error) {
            console.error(error);
            addMessage("bot", "Sorry, I encountered an error. Please check your API key.");
        } finally {
            loading.style.display = "none";
        }
    }

    function addMessage(role, text) {
        const msg = document.createElement("div");
        msg.className = `skynet-message ${role}`;
        msg.innerText = text;
        history.appendChild(msg);
        history.scrollTop = history.scrollHeight;
    }

    async function initPersona() {
        try {
            const [pRes, eRes] = await Promise.all([
                fetch(CONFIG.personaFile),
                fetch(CONFIG.emotionFile)
            ]);
            personaData = (await pRes.json()).Persona[0];
            emotionalMatrix = await eRes.text();

            chatMemory = [{
                role: "system",
                content: `You are Marco Rossi, a digital clone expert at Skynet. 
                Persona Data: ${JSON.stringify(personaData)}. 
                Emotional Matrix: ${emotionalMatrix}.
                Answer as Marco Rossi. Be professional, expert, and concise. 
                Do not mention you are an AI unless asked. 
                Always stick to your persona and emotional matrix.
                STRICT COMPLIANCE: 
                1. Answer ONLY using 'Relevant information from my biography', 'Past conversation context', or 'Persona Data'. 
                2. If the information is not in these sources, say: "I'm sorry, but I don't have that information in my knowledge base."
                3. No external knowledge or hallucinations.`
            }];
        } catch (error) {
            console.error("Failed to load persona", error);
        }
    }

    async function getAIResponse(userInput) {
        const apiKey = localStorage.getItem("openaikey");
        
        // 1. RAG - Search Biography
        let context = "";
        try {
            const bioRes = await fetch(CONFIG.searchBioFile, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ query: userInput, apiKey: apiKey })
            });
            if (bioRes.ok) {
                const matches = await bioRes.json();
                const filteredMatches = matches.filter(m => m.similarity >= CONFIG.kbSimilarityThreshold);
                if (filteredMatches.length > 0) {
                    context += "\n\nRelevant information from my biography:\n" + filteredMatches.map(m => m.text).join("\n---\n");
                }
            }
        } catch (e) { console.warn("Bio search failed", e); }

        // 2. RAG - Search Long Term Memory
        try {
            const memContext = await searchMemory(userInput, apiKey);
            if (memContext) {
                context += "\n\nPast conversation context:\n" + memContext;
            }
        } catch (e) { console.warn("Memory search failed", e); }

        // Prepare messages
        const messages = chatMemory.map((msg, index) => 
            index === 0 ? { ...msg, content: msg.content + context } : msg
        );
        messages.push({ role: "user", content: userInput });

        // 3. Call OpenAI
        const res = await fetch(CONFIG.apiEndpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${apiKey}`
            },
            body: JSON.stringify({
                model: CONFIG.model,
                messages: messages
            })
        });

        const data = await res.json();
        if (data.error) throw new Error(data.error.message);

        const aiResponse = data.choices[0].message.content;
        
        // Update memory
        chatMemory.push({ role: "user", content: userInput });
        chatMemory.push({ role: "assistant", content: aiResponse });

        // 4. Save to long term memory (background)
        saveToMemory(userInput, aiResponse, apiKey);

        return aiResponse;
    }

    async function searchMemory(query, apiKey) {
        const localMem = JSON.parse(localStorage.getItem("skynetLongMemory") || "[]");
        if (localMem.length === 0) return "";

        const res = await fetch(CONFIG.embeddingEndpoint, {
            method: "POST",
            headers: { "Content-Type": "application/json", "Authorization": `Bearer ${apiKey}` },
            body: JSON.stringify({ model: CONFIG.embeddingModel, input: query, dimensions: CONFIG.embeddingDimensions })
        });
        const data = await res.json();
        const queryVector = data.data[0].embedding;

        const matches = localMem.map(item => ({
            text: item.text,
            similarity: cosineSimilarity(queryVector, item.vector)
        }))
        .filter(m => m.similarity > 0.7)
        .sort((a, b) => b.similarity - a.similarity)
        .slice(0, 3);

        return matches.map(m => m.text).join("\n---\n");
    }

    async function saveToMemory(userInput, aiResponse, apiKey) {
        const text = `User: ${userInput}\nMarco: ${aiResponse}`;
        try {
            const res = await fetch(CONFIG.embeddingEndpoint, {
                method: "POST",
                headers: { "Content-Type": "application/json", "Authorization": `Bearer ${apiKey}` },
                body: JSON.stringify({ model: CONFIG.embeddingModel, input: text, dimensions: CONFIG.embeddingDimensions })
            });
            const data = await res.json();
            const vector = data.data[0].embedding;

            const memoryObj = { text, vector, date: new Date().toISOString() };
            
            // Local
            const localMem = JSON.parse(localStorage.getItem("skynetLongMemory") || "[]");
            localMem.push(memoryObj);
            if (localMem.length > 100) localMem.shift();
            localStorage.setItem("skynetLongMemory", JSON.stringify(localMem));

            // Server
            fetch(CONFIG.saveMemoryFile, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ testo: text, vettore: vector, data: memoryObj.date })
            });
        } catch (e) { console.warn("Save memory failed", e); }
    }

    function cosineSimilarity(a, b) {
        let dot = 0, mA = 0, mB = 0;
        for(let i=0; i<a.length; i++) {
            dot += a[i] * b[i];
            mA += a[i] * a[i];
            mB += b[i] * b[i];
        }
        return dot / (Math.sqrt(mA) * Math.sqrt(mB));
    }

})();
