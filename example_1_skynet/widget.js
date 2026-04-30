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
        uploadFile: "widget_vupload.php",
        logChatFile: "widget_log_chat.php",
        model: "gpt-4o-mini",
        visionModel: "gpt-4.1-mini",
        embeddingModel: "text-embedding-3-small",
        embeddingDimensions: 512,
        kbSimilarityThreshold: 0.5,
        maxVisionTokens: 28000
    };

    let chatMemory = [];
    let personaData = null;
    let emotionalMatrix = "";
    let isOpen = false;

    // Load styles
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = "widget.css?v=<?php echo time(); ?>";
    document.head.appendChild(link);

    // Create Widget HTML
    const container = document.createElement("div");
    container.className = "skynet-widget-container";
    container.innerHTML = `
        <div class="skynet-widget-trigger" id="skynet-trigger">
            <span style="font-size: 30px;">🤖</span>
        </div>
        <div class="skynet-chat-window" id="skynet-chat-window">
            <div class="skynet-chat-header" id="skynet-chat-header">
                <h3>Skynet Expert: <span id="skynet-persona-name">Marco Rossi</span></h3>
                <span class="skynet-close-btn" id="skynet-close">&times;</span>
            </div>
            <div class="skynet-chat-history" id="skynet-history">
                <div class="skynet-message bot" id="skynet-initial-message">Hello! I am Marco Rossi, an expert here at Skynet. How can I assist you today with AI, defense, or robotics?</div>
            </div>
            <div id="skynet-loading" class="skynet-loading" style="padding: 0 12px;">Marco is thinking...</div>
            <div class="skynet-chat-input-area">
                <input type="file" id="skynet-image-input" accept="image/*" style="display: none;">
                <button class="skynet-upload-btn" id="skynet-upload" title="Upload Image">📎</button>
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
    const uploadBtn = document.getElementById("skynet-upload");
    const imageInput = document.getElementById("skynet-image-input");
    const initialMessage = document.getElementById("skynet-initial-message");

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

    // Handle image upload button
    uploadBtn.onclick = () => {
        imageInput.click();
    };

    // Handle image selection
    imageInput.onchange = async (e) => {
        const file = e.target.files[0];
        if (file) {
            await handleImageUpload(file);
        }
    };

    // Handle Send
    sendBtn.onclick = sendMessage;
    input.onkeydown = (e) => {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    };

    async function handleImageUpload(file) {
        const apiKey = localStorage.getItem("openaikey");
        if (!apiKey) {
            const key = prompt("Please enter your OpenAI API Key:");
            if (key) {
                localStorage.setItem("openaikey", key);
            } else {
                return;
            }
        }

        addImageMessage("user", file);
        loading.style.display = "block";
        loading.innerText = "Uploading and analyzing image...";

        try {
            // Upload image to server
            const formData = new FormData();
            formData.append("file", file);

            const uploadRes = await fetch(CONFIG.uploadFile, {
                method: "POST",
                body: formData
            });
            const uploadData = await uploadRes.json();

            if (uploadData.status !== "success") {
                throw new Error(uploadData.message || "Upload failed");
            }

            const imageUrl = uploadData.url;
            
            // Analyze image with Vision
            const analysis = await analyzeImage(imageUrl, apiKey);
            addMessage("bot", analysis);
            
            // Save to long-term memory
            await saveImageToMemory(imageUrl, analysis, apiKey);
            
            // Log the chat
            await logChat("", imageUrl, analysis, apiKey);

        } catch (error) {
            console.error(error);
            addMessage("bot", "Sorry, I encountered an error analyzing the image. Please try again.");
        } finally {
            loading.style.display = "none";
            loading.innerText = "Marco is thinking...";
            imageInput.value = "";
        }
    }

    async function analyzeImage(imageUrl, apiKey) {
        const fullName = personaData ? `${personaData.Name} ${personaData.Surname}` : "Marco Rossi";
        
        const visionPrompt = `You are ${fullName}, an expert at Skynet specializing in Artificial Intelligence, Defense Systems, and advanced Robotics.

Analyze this image carefully and provide a detailed response in the style of an expert consultation. Consider:

1. What does the image contain? Provide a thorough description.
2. If there are technical elements (code, diagrams, schematics, engineering drawings), analyze them in detail.
3. If there are people, describe their appearance, activities, and context.
4. If applicable, relate the image content to AI, defense systems, robotics, or technology topics.
5. Provide expert insights and commentary relevant to your area of expertise.

Persona: ${JSON.stringify(personaData)}
Emotional Matrix: ${emotionalMatrix}

Respond as ${fullName} would, maintaining professional expertise and analytical perspective. Include your expert opinion on any notable aspects of the image.`;

        const response = await fetch(CONFIG.apiEndpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${apiKey}`
            },
            body: JSON.stringify({
                model: CONFIG.visionModel,
                messages: [
                    {
                        role: "user",
                        content: [
                            {
                                type: "text",
                                text: visionPrompt
                            },
                            {
                                type: "image_url",
                                image_url: { url: imageUrl }
                            }
                        ]
                    }
                ],
                max_tokens: CONFIG.maxVisionTokens
            })
        });

        const data = await response.json();
        if (data.error) throw new Error(data.error.message);

        return data.choices[0].message.content;
    }

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
            
            // Log the chat
            await logChat(text, null, response, apiKey);
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

    function addImageMessage(role, file) {
        const msg = document.createElement("div");
        msg.className = `skynet-message ${role}`;
        
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.className = "skynet-uploaded-image";
        img.onload = () => URL.revokeObjectURL(img.src);
        
        const caption = document.createElement("span");
        caption.className = "skynet-image-caption";
        caption.innerText = file.name;
        
        msg.appendChild(img);
        msg.appendChild(caption);
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

            // Update dynamic identity
            const nameSpan = document.getElementById("skynet-persona-name");
            if (nameSpan && personaData) {
                nameSpan.textContent = `${personaData.Name} ${personaData.Surname}`;
            }

            // Update initial message if available
            if (initialMessage && personaData && personaData.InitialMessage) {
                initialMessage.innerText = personaData.InitialMessage;
            }

            const fullName = personaData ? `${personaData.Name} ${personaData.Surname}` : "Marco Rossi";

            chatMemory = [{
                role: "system",
                content: `You are ${fullName}, a digital clone expert at Skynet. 
                Persona Data: ${JSON.stringify(personaData)}. 
                Emotional Matrix: ${emotionalMatrix}.
                Answer as ${fullName}. Be professional, expert, and concise. 
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

    async function saveImageToMemory(imageUrl, analysis, apiKey) {
        const text = `Image Analysis: ${imageUrl}\nMarco's Analysis: ${analysis}`;
        try {
            const res = await fetch(CONFIG.embeddingEndpoint, {
                method: "POST",
                headers: { "Content-Type": "application/json", "Authorization": `Bearer ${apiKey}` },
                body: JSON.stringify({ model: CONFIG.embeddingModel, input: text, dimensions: CONFIG.embeddingDimensions })
            });
            const data = await res.json();
            const vector = data.data[0].embedding;

            const memoryObj = { text, vector, date: new Date().toISOString(), type: "image_analysis", imageUrl: imageUrl };
            
            // Local
            const localMem = JSON.parse(localStorage.getItem("skynetLongMemory") || "[]");
            localMem.push(memoryObj);
            if (localMem.length > 100) localMem.shift();
            localStorage.setItem("skynetLongMemory", JSON.stringify(localMem));

            // Server
            fetch(CONFIG.saveMemoryFile, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ testo: text, vettore: vector, data: memoryObj.date, type: "image_analysis", imageUrl: imageUrl })
            });
        } catch (e) { console.warn("Save image memory failed", e); }
    }

    async function logChat(userText, imageUrl, response, apiKey) {
        try {
            await fetch(CONFIG.logChatFile, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    userText: userText,
                    imageUrl: imageUrl,
                    response: response,
                    timestamp: new Date().toISOString(),
                    persona: personaData ? `${personaData.Name} ${personaData.Surname}` : "Marco Rossi"
                })
            });
        } catch (e) { console.warn("Log chat failed", e); }
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