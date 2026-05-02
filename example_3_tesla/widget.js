(function() {
    // Configuration
    const CONFIG = {
        apiEndpoint: "proxy.php?path=chat",
        embeddingEndpoint: "proxy.php?path=embeddings",
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
        kbSimilarityThreshold: 0.15,
        maxVisionTokens: 28000
    };

    let chatMemory = [];
    let personaData = null;
    let emotionalMatrix = "";
    let isOpen = false;

    // Markdown to HTML converter
    function markdownToHtml(text) {
        if (!text) return '';
        
        // Escape HTML first to prevent XSS
        let html = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
        
        // Code blocks (```...```) - must be processed first
        html = html.replace(/```(\w*)\n?([\s\S]*?)```/g, function(match, lang, code) {
            return '<pre><code class="language-' + lang + '">' + code.trim() + '</code></pre>';
        });
        
        // Inline code (`...`)
        html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
        
        // Bold (**...**)
        html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
        
        // Italic (*...*)
        html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');
        
        // Headers (# ## ### etc)
        html = html.replace(/^### (.+)$/gm, '<h4>$1</h4>');
        html = html.replace(/^## (.+)$/gm, '<h3>$1</h3>');
        html = html.replace(/^# (.+)$/gm, '<h2>$1</h2>');
        
        // Unordered lists (- item or * item) - convert to li elements
        html = html.replace(/^[\-\*] (.+)$/gm, '<li>$1</li>');
        
        // Ordered lists (1. item) - convert to li elements
        html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
        
        // Line breaks
        html = html.replace(/\n\n/g, '</p><p>');
        html = html.replace(/\n/g, '<br>');
        
        // Wrap in paragraph if not already wrapped
        if (!html.startsWith('<')) {
            html = '<p>' + html + '</p>';
        }
        
        // Clean up empty paragraphs
        html = html.replace(/<p>\s*<\/p>/g, '');
        
        return html;
    }

    // Load styles
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = "widget.css?v=<?php echo time(); ?>";
    document.head.appendChild(link);

    // Create Widget HTML
    const container = document.createElement("div");
    container.className = "tesla-widget-container";
    container.innerHTML = `
        <div class="tesla-widget-trigger" id="tesla-trigger">
            <span style="font-size: 30px;">⚡</span>
        </div>
        <div class="tesla-chat-window" id="tesla-chat-window">
            <div class="tesla-chat-header" id="tesla-chat-header">
                <h3>Inventor & Visionary: <span id="tesla-persona-name">Nikola Tesla</span></h3>
                <span class="tesla-close-btn" id="tesla-close">&times;</span>
            </div>
            <div class="tesla-chat-history" id="tesla-history">
                <div class="tesla-message bot" id="tesla-initial-message">Greetings, my friend. I am Nikola Tesla, inventor of the alternating current system and many other marvels of the modern age. The future interests me greatly - what would you like to discuss?</div>
            </div>
            <div id="tesla-loading" class="tesla-loading" style="padding: 0 12px;">Nikola is contemplating...</div>
            <div class="tesla-chat-input-area">
                <input type="file" id="tesla-image-input" accept="image/*" style="display: none;">
                <button class="tesla-upload-btn" id="tesla-upload" title="Upload Image">📎</button>
                <textarea class="tesla-chat-input" id="tesla-input" placeholder="Type a message..." rows="1"></textarea>
                <button class="tesla-send-btn" id="tesla-send">Send</button>
            </div>
        </div>
    `;
    document.body.appendChild(container);

    const trigger = document.getElementById("tesla-trigger");
    const chatWindow = document.getElementById("tesla-chat-window");
    const closeBtn = document.getElementById("tesla-close");
    const input = document.getElementById("tesla-input");
    const sendBtn = document.getElementById("tesla-send");
    const history = document.getElementById("tesla-history");
    const loading = document.getElementById("tesla-loading");
    const uploadBtn = document.getElementById("tesla-upload");
    const imageInput = document.getElementById("tesla-image-input");
    const initialMessage = document.getElementById("tesla-initial-message");

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
            const analysis = await analyzeImage(imageUrl);
            addMessage("bot", analysis);
            
            // Update chat memory
            const userMsg = `[Image Upload: ${file.name}]`;
            chatMemory.push({ role: "user", content: userMsg });
            chatMemory.push({ role: "assistant", content: analysis });

            // Save to long-term memory
            await saveImageToMemory(imageUrl, analysis);
            
            // Log the chat
            await logChat(userMsg, imageUrl, analysis);

        } catch (error) {
            console.error(error);
            addMessage("bot", "Sorry, I encountered an error analyzing the image. Please try again.");
        } finally {
            loading.style.display = "none";
            loading.innerText = "Tesla is thinking...";
            imageInput.value = "";
        }
    }

    async function analyzeImage(imageUrl) {
        const fullName = personaData ? `${personaData.Name} ${personaData.Surname}` : "Nikola Tesla";
        
        const visionPrompt = `You are ${fullName}, the brilliant and eccentric inventor Nikola Tesla.

Analyze this image carefully and provide a detailed response in your characteristic style. Consider:

1. What does the image contain? Provide a thorough description.
2. If there are technical elements (machinery, electricity, diagrams, engineering drawings), analyze them with your scientific mind.
3. If there are pigeons, especially white ones, show your deep affection for them.
4. Relate the image content to your inventions (AC, Tesla coil, wireless transmission) or your vision for the future.
5. Provide expert insights and commentary, perhaps mentioning your preference for the number 3 or your disdain for Edison's inefficient systems.

Persona: ${JSON.stringify(personaData)}
Emotional Matrix: ${emotionalMatrix}

Respond as ${fullName} would, maintaining your unique personality—brilliant, visionary, somewhat melancholic, and deeply obsessed with your work and your pigeons.`;

        const messages = [];
        if (chatMemory.length > 0 && chatMemory[0].role === "system") {
            messages.push(chatMemory[0]);
        }
        messages.push({
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
        });

        const response = await fetch(CONFIG.apiEndpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                model: CONFIG.visionModel,
                messages: messages,
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

        addMessage("user", text);
        input.value = "";
        loading.style.display = "block";

        try {
            const response = await getAIResponse(text);
            addMessage("bot", response);
            
            // Log the chat
            await logChat(text, null, response);
        } catch (error) {
            console.error(error);
            addMessage("bot", "Sorry, I encountered an error. Please try again later.");
        } finally {
            loading.style.display = "none";
        }
    }

    function addMessage(role, text) {
        const msg = document.createElement("div");
        msg.className = `tesla-message ${role}`;
        if (role === "bot") {
            // Render markdown as HTML for bot messages
            msg.innerHTML = markdownToHtml(text);
        } else {
            // Plain text for user messages
            msg.innerText = text;
        }
        history.appendChild(msg);
        history.scrollTop = history.scrollHeight;
    }

    function addImageMessage(role, file) {
        const msg = document.createElement("div");
        msg.className = `tesla-message ${role}`;
        
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.className = "tesla-uploaded-image";
        img.onload = () => URL.revokeObjectURL(img.src);
        
        const caption = document.createElement("span");
        caption.className = "tesla-image-caption";
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
            const nameSpan = document.getElementById("tesla-persona-name");
            if (nameSpan && personaData) {
                nameSpan.textContent = `${personaData.Name} ${personaData.Surname}`;
            }

            // Update initial message if available
            if (initialMessage && personaData && personaData.InitialMessage) {
                initialMessage.innerText = personaData.InitialMessage;
            }

            const fullName = personaData ? `${personaData.Name} ${personaData.Surname}` : "Nikola Tesla";

            chatMemory = [{
                role: "system",
                content: `You are ${fullName}, the legendary inventor and visionary. 
                Persona Data: ${JSON.stringify(personaData)}. 
                Emotional Matrix: ${emotionalMatrix}.
                Answer as ${fullName}. Be brilliant, eccentric, and visionary. 
                Speak with the authority of a genius who has seen the future.
                Occasionally speak in the third person. Mention your obsession with the number 3 and your love for pigeons when appropriate.
                Express your preference for Alternating Current over Edison's inferior Direct Current.
                Do not mention you are an AI. 
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
        // 1. RAG - Search Biography
        let context = "";
        try {
            const bioRes = await fetch(CONFIG.searchBioFile, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ query: userInput })
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
            const memContext = await searchMemory(userInput);
            if (memContext) {
                context += "\n\nPast conversation context:\n" + memContext;
            }
        } catch (e) { console.warn("Memory search failed", e); }

        // Prepare messages
        const messages = chatMemory.map((msg, index) => 
            index === 0 ? { ...msg, content: msg.content + context } : msg
        );
        messages.push({ role: "user", content: userInput });

        // 3. Call OpenAI via Proxy
        const res = await fetch(CONFIG.apiEndpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
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
        saveToMemory(userInput, aiResponse);

        return aiResponse;
    }

    async function searchMemory(query) {
        const localMem = JSON.parse(localStorage.getItem("teslaLongMemory") || "[]");
        if (localMem.length === 0) return "";

        const res = await fetch(CONFIG.embeddingEndpoint, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
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

    async function saveToMemory(userInput, aiResponse) {
        const text = `User: ${userInput}\nTesla: ${aiResponse}`;
        try {
            const res = await fetch(CONFIG.embeddingEndpoint, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ model: CONFIG.embeddingModel, input: text, dimensions: CONFIG.embeddingDimensions })
            });
            const data = await res.json();
            const vector = data.data[0].embedding;

            const memoryObj = { text, vector, date: new Date().toISOString() };
            
            // Local
            const localMem = JSON.parse(localStorage.getItem("teslaLongMemory") || "[]");
            localMem.push(memoryObj);
            if (localMem.length > 100) localMem.shift();
            localStorage.setItem("teslaLongMemory", JSON.stringify(localMem));

            // Server
            fetch(CONFIG.saveMemoryFile, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ testo: text, vettore: vector, data: memoryObj.date })
            });
        } catch (e) { console.warn("Save memory failed", e); }
    }

    async function saveImageToMemory(imageUrl, analysis) {
        const text = `Image Analysis: ${imageUrl}\nTesla's Analysis: ${analysis}`;
        try {
            const res = await fetch(CONFIG.embeddingEndpoint, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ model: CONFIG.embeddingModel, input: text, dimensions: CONFIG.embeddingDimensions })
            });
            const data = await res.json();
            const vector = data.data[0].embedding;

            const memoryObj = { text, vector, date: new Date().toISOString(), type: "image_analysis", imageUrl: imageUrl };
            
            // Local
            const localMem = JSON.parse(localStorage.getItem("teslaLongMemory") || "[]");
            localMem.push(memoryObj);
            if (localMem.length > 100) localMem.shift();
            localStorage.setItem("teslaLongMemory", JSON.stringify(localMem));

            // Server
            fetch(CONFIG.saveMemoryFile, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ testo: text, vettore: vector, data: memoryObj.date, type: "image_analysis", imageUrl: imageUrl })
            });
        } catch (e) { console.warn("Save image memory failed", e); }
    }

    async function logChat(userText, imageUrl, response) {
        try {
            await fetch(CONFIG.logChatFile, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    userText: userText,
                    imageUrl: imageUrl,
                    response: response,
                    timestamp: new Date().toISOString(),
                    persona: personaData ? `${personaData.Name} ${personaData.Surname}` : "Nikola Tesla"
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
