<?php
/**
 * widget_admin.php
 * Admin interface for the Skynet Digital Clone Widget
 * Allows uploading PDFs, extracting text via OpenAI, and updating the knowledgebase
 */

// Simple password protection (change this for production)
$adminPassword = 'skynet_admin_2024';
$isAuthenticated = false;

require_once 'api_config.php';

// Check authentication
session_start();
if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true) {
    $isAuthenticated = true;
}

// Handle login
if (isset($_POST['password']) && $_POST['password'] === $adminPassword) {
    $_SESSION['admin_auth'] = true;
    $isAuthenticated = true;
}

// Handle API Key Encoding & Saving
if ($isAuthenticated && isset($_GET['action']) && $_GET['action'] === 'encode_api_key' && isset($_POST['plain_key'])) {
    header('Content-Type: application/json');
    $plainKey = $_POST['plain_key'];
    
    if (empty($plainKey)) {
        echo json_encode(['status' => 'error', 'message' => 'API Key cannot be empty']);
        exit;
    }
    
    $encodedKey = encodeKey($plainKey);
    $configFile = __DIR__ . '/api_config.php';
    $configContent = file_get_contents($configFile);
    
    // Replace the OBFUSCATED_API_KEY value
    $pattern = '/\$OBFUSCATED_API_KEY\s*=\s*\'[^\']*\';/';
    $replacement = "\$OBFUSCATED_API_KEY = '$encodedKey';";
    $newConfigContent = preg_replace($pattern, $replacement, $configContent);
    
    if ($newConfigContent !== null && $newConfigContent !== $configContent) {
        if (file_put_contents($configFile, $newConfigContent)) {
            echo json_encode(['status' => 'success', 'message' => 'API Key encoded and saved successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to write to api_config.php. Check file permissions.']);
        }
    } else if ($newConfigContent === $configContent) {
         echo json_encode(['status' => 'success', 'message' => 'API Key is already set to this value.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update api_config.php. Pattern not found.']);
    }
    exit;
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    $isAuthenticated = false;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zax Widget Admin - Planetary Acquisition Management</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #000000, #1a1a2e, #16213e);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            font-size: 1.8rem;
            background: linear-gradient(90deg, #00ff41, #008f11);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }
        .card {
            background: rgba(0, 255, 65, 0.05);
            border: 1px solid rgba(0, 255, 65, 0.2);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }
        .card h2 {
            font-size: 1.2rem;
            color: #00ff41;
            margin-bottom: 16px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            font-size: 0.85rem;
            color: rgba(0, 255, 65, 0.7);
            margin-bottom: 6px;
        }
        input[type="password"], input[type="text"], textarea {
            width: 100%;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(0, 255, 65, 0.1);
            border-radius: 8px;
            color: #00ff41;
            padding: 10px 12px;
            font-size: 0.9rem;
            font-family: inherit;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: rgba(0, 255, 65, 0.5);
        }
        input[type="file"] {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1px dashed rgba(0, 255, 65, 0.3);
            border-radius: 8px;
            padding: 12px;
            color: rgba(0, 255, 65, 0.6);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid rgba(0, 255, 65, 0.1);
            background: rgba(0, 255, 65, 0.08);
            color: #fff;
            transition: all 0.2s;
            font-family: inherit;
        }
        .btn:hover {
            background: rgba(0, 255, 65, 0.25);
            border-color: rgba(0, 255, 65, 0.4);
        }
        .btn-primary {
            background: linear-gradient(135deg, #00ff41, #008f11);
            color: #000;
            border: none;
        }
        .btn-primary:hover {
            box-shadow: 0 4px 15px rgba(0, 255, 65, 0.4);
        }
        .btn-danger {
            background: rgba(255,80,80,0.2);
            border-color: rgba(255,80,80,0.3);
            color: #ff6b6b;
        }
        .feedback {
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 16px;
            font-size: 0.85rem;
        }
        .feedback.success {
            background: rgba(0, 255, 65, 0.15);
            border: 1px solid rgba(0, 255, 65, 0.3);
            color: #00ff41;
        }
        .feedback.error {
            background: rgba(255,80,80,0.15);
            border: 1px solid rgba(255,80,80,0.3);
            color: #ff6b6b;
        }
        .file-list {
            margin-top: 16px;
        }
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            background: rgba(0, 255, 65, 0.04);
            border: 1px solid rgba(0, 255, 65, 0.08);
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .file-name {
            font-size: 0.9rem;
            color: rgba(0, 255, 65, 0.8);
        }
        .file-date {
            font-size: 0.75rem;
            color: rgba(0, 255, 65, 0.4);
        }
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-indicator.online { background: #00ff41; }
        .status-indicator.offline { background: #ff6b6b; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 16px;
        }
        .stat-card {
            background: rgba(0, 255, 65, 0.04);
            border: 1px solid rgba(0, 255, 65, 0.08);
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #00ff41;
        }
        .stat-label {
            font-size: 0.75rem;
            color: rgba(0, 255, 65, 0.5);
            margin-top: 4px;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid rgba(0, 255, 65, 0.2);
            border-top-color: #00ff41;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 12px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .extracted-preview {
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(0, 255, 65, 0.15);
            border-radius: 8px;
            padding: 12px;
            margin-top: 12px;
            max-height: 200px;
            overflow-y: auto;
            font-size: 0.8rem;
            color: rgba(0, 255, 65, 0.8);
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👽 Zax Widget Admin - Planetary Acquisition Management</h1>
        
        <?php if (!$isAuthenticated): ?>
        <!-- Login Form -->
        <div class="card">
            <h2>🔐 Security Clearance Required</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="password">Access Code</label>
                    <input type="password" id="password" name="password" placeholder="Enter access code" required>
                </div>
                <button type="submit" class="btn btn-primary">Authenticate</button>
            </form>
        </div>
        <?php else: ?>
        
        <!-- Logout -->
        <div style="text-align: right; margin-bottom: 20px;">
            <a href="?action=logout" class="btn btn-danger">Abort Session</a>
        </div>
        
        <!-- API Key Setup (New Section) -->
        <div class="card" id="apiKeySetupSection">
            <h2>🔑 Galactic API Key</h2>
            <p style="font-size: 0.85rem; color: rgba(255,255,255,0.6); margin-bottom: 16px;">
                Enter your plain OpenAI API key here. It will be encoded and saved to <code>api_config.php</code> server-side.
            </p>
            <div class="form-group">
                <label for="plainApiKey">Plain API Key</label>
                <div style="display: flex; gap: 8px;">
                    <input type="password" id="plainApiKey" placeholder="sk-..." style="font-family: monospace;">
                    <button type="button" class="btn" id="toggleKeyVisibility" title="Show/Hide Key">👁️</button>
                </div>
            </div>
            <div class="warning-text" style="background: rgba(255,165,0,0.1); border-left: 4px solid #ffa500; padding: 10px; margin-bottom: 16px; font-size: 0.8rem;">
                <strong>⚠️ Security Warning:</strong> This will update the server-side configuration file.
            </div>
            <button id="encodeSaveBtn" class="btn btn-primary">Authorize & Save</button>
            <div id="setupFeedback" class="feedback" style="display: none;"></div>
        </div>
        
        <!-- API Key Configuration -->
        <div class="card">
            <h2>⚙️ Comm Configuration</h2>
            <p style="font-size: 0.9rem; color: rgba(255,255,255,0.8); margin-bottom: 12px;">
                API Key is managed server-side via <code>api_config.php</code>
            </p>
            <button onclick="document.getElementById('apiKeySetupSection').scrollIntoView({behavior: 'smooth'})" class="btn">Update Key</button>
        </div>
        
        <!-- PDF Upload -->
        <div class="card">
            <h2>📄 Scan Document to Knowledgebase</h2>
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="pdfFile">Select PDF (max 20MB)</label>
                    <input type="file" id="pdfFile" name="pdfFile" accept=".pdf">
                </div>
                <div class="form-group">
                    <label for="docName">Data Stream Name</label>
                    <input type="text" id="docName" name="docName" placeholder="e.g., human_weaknesses">
                </div>
                <button type="submit" class="btn btn-primary">Scan & Analyze</button>
            </form>
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Decoding data stream... Please wait.</p>
            </div>
            <div id="uploadFeedback" class="feedback" style="display: none;"></div>
            <div id="extractedPreview" class="extracted-preview" style="display: none;"></div>
        </div>
        
        <!-- Manual Text Entry -->
        <div class="card">
            <h2>✏️ Manual Intelligence Entry</h2>
            <form id="textForm">
                <div class="form-group">
                    <label for="textTitle">Intelligence Title</label>
                    <input type="text" id="textTitle" name="textTitle" placeholder="e.g., tactical_notes">
                </div>
                <div class="form-group">
                    <label for="textContent">Intelligence Content</label>
                    <textarea id="textContent" name="textContent" rows="6" placeholder="Enter data to add to the knowledgebase..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Commit to Knowledgebase</button>
            </form>
            <div id="textFeedback" class="feedback" style="display: none;"></div>
        </div>
        
        <!-- Index Biography -->
        <div class="card">
            <h2>🗂 Re-calculate Intelligence Index</h2>
            <p style="font-size: 0.85rem; color: rgba(255,255,255,0.6); margin-bottom: 16px;">
                This will regenerate the intelligence matrix from all data files in the bio/ directory.
            </p>
            <button id="indexBtn" class="btn btn-primary">🔄 Sync Intelligence</button>
            <div id="indexFeedback" class="feedback" style="display: none;"></div>
        </div>
        
        <!-- Knowledgebase Files -->
        <div class="card">
            <h2>📚 Intelligence Repositories</h2>
            <div class="file-list" id="fileList">
                <p style="color: rgba(255,255,255,0.5); font-size: 0.85rem;">Scanning repositories...</p>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="card">
            <h2>📊 Strategic Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" id="bioCount">-</div>
                    <div class="stat-label">Intelligence Units</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="logCount">-</div>
                    <div class="stat-label">Intercepted Comms</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="memoryCount">-</div>
                    <div class="stat-label">Memory Fragments</div>
                </div>
            </div>
        </div>
        
        <script>
            // Server-side API key for client-side operations
            const serverApiKey = <?php echo json_encode($isAuthenticated ? getOpenAIKey() : ""); ?>;

            // Toggle API Key visibility
            document.getElementById('toggleKeyVisibility')?.addEventListener('click', function() {
                const input = document.getElementById('plainApiKey');
                if (input.type === 'password') {
                    input.type = 'text';
                    this.textContent = '🙈';
                } else {
                    input.type = 'password';
                    this.textContent = '👁️';
                }
            });

            // Encode & Save API Key
            document.getElementById('encodeSaveBtn')?.addEventListener('click', async function() {
                const plainKey = document.getElementById('plainApiKey').value.trim();
                const feedback = document.getElementById('setupFeedback');

                if (!plainKey) {
                    showFeedback('setupFeedback', 'error', 'Please enter an API Key.');
                    return;
                }

                this.disabled = true;
                this.innerHTML = '⏳ Encoding & Saving...';

                try {
                    const formData = new FormData();
                    formData.append('plain_key', plainKey);

                    const res = await fetch('?action=encode_api_key', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await res.json();

                    if (data.status === 'success') {
                        showFeedback('setupFeedback', 'success', data.message);
                        // Reload the page after a short delay to use the new key
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showFeedback('setupFeedback', 'error', data.message);
                    }
                } catch (error) {
                    showFeedback('setupFeedback', 'error', 'Error: ' + error.message);
                } finally {
                    this.disabled = false;
                    this.innerHTML = 'Encode & Save';
                }
            });

            // PDF Upload Form
            document.getElementById('uploadForm')?.addEventListener('submit', async function(e) {
                e.preventDefault();

                const apiKey = serverApiKey;
                if (!apiKey) {
                    showFeedback('uploadFeedback', 'error', 'API Key is not configured on server. Please use the API Key Setup section above.');
                    return;
                }

                const fileInput = document.getElementById('pdfFile');
                const docName = document.getElementById('docName').value.trim();
                
                if (!fileInput.files[0]) {
                    showFeedback('uploadFeedback', 'error', 'Please select a PDF file.');
                    return;
                }
                
                if (!docName) {
                    showFeedback('uploadFeedback', 'error', 'Please enter a document name.');
                    return;
                }
                
                const file = fileInput.files[0];
                if (file.size > 20 * 1024 * 1024) {
                    showFeedback('uploadFeedback', 'error', 'File size exceeds 20MB limit.');
                    return;
                }
                
                document.getElementById('loading').style.display = 'block';
                document.getElementById('uploadFeedback').style.display = 'none';
                
                try {
                    // Step 1: Upload PDF to OpenAI
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('purpose', 'assistants');
                    
                    const uploadRes = await fetch('https://api.openai.com/v1/files', {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + apiKey },
                        body: formData
                    });
                    
                    if (!uploadRes.ok) {
                        throw new Error('Failed to upload file to OpenAI');
                    }
                    
                    const uploadData = await uploadRes.json();
                    const fileId = uploadData.id;
                    
                    // Step 2: Extract text using GPT-4.1-mini
                    const extractRes = await fetch('https://api.openai.com/v1/chat/completions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + apiKey
                        },
                        body: JSON.stringify({
                            model: 'gpt-4.1-mini',
                            messages: [
                                {
                                    role: 'user',
                                    content: [
                                        {
                                            type: 'text',
                                            text: 'Extract all text content from this PDF document. Preserve the structure and formatting as much as possible. Return ONLY the extracted text, no explanations or summaries.'
                                        },
                                        {
                                            type: 'file',
                                            file: { file_id: fileId }
                                        }
                                    ]
                                }
                            ],
                            max_tokens: 16000
                        })
                    });
                    
                    if (!extractRes.ok) {
                        throw new Error('Failed to extract text from PDF');
                    }
                    
                    const extractData = await extractRes.json();
                    const extractedText = extractData.choices[0].message.content;
                    
                    // Show preview
                    const preview = document.getElementById('extractedPreview');
                    preview.textContent = extractedText.substring(0, 1000) + (extractedText.length > 1000 ? '...' : '');
                    preview.style.display = 'block';
                    
                    // Step 3: Save to bio/ directory via server
                    const saveRes = await fetch('widget_save_bio.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            filename: docName + '.txt',
                            content: extractedText
                        })
                    });
                    
                    if (!saveRes.ok) {
                        throw new Error('Failed to save extracted text');
                    }
                    
                    // Step 4: Rebuild index
                    await rebuildIndex();
                    
                    showFeedback('uploadFeedback', 'success', 
                        'PDF processed successfully! Extracted ' + extractedText.length + ' characters. File saved as ' + docName + '.txt');
                    
                    loadFileList();
                    updateStats();
                    
                } catch (error) {
                    showFeedback('uploadFeedback', 'error', 'Error: ' + error.message);
                } finally {
                    document.getElementById('loading').style.display = 'none';
                }
            });
            
            // Text Entry Form
            document.getElementById('textForm')?.addEventListener('submit', async function(e) {
                e.preventDefault();

                const title = document.getElementById('textTitle').value.trim();
                const content = document.getElementById('textContent').value.trim();
                const apiKey = serverApiKey;

                if (!title || !content) {
                    showFeedback('textFeedback', 'error', 'Please fill in both title and content.');
                    return;
                }

                if (!apiKey) {
                    showFeedback('textFeedback', 'error', 'API Key is not configured on server. Please use the API Key Setup section above.');
                    return;
                }

                try {
                    const res = await fetch('widget_save_bio.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            filename: title + '.txt',
                            content: content
                        })
                    });

                    if (!res.ok) {
                        throw new Error('Failed to save text');
                    }

                    // Rebuild index
                    await rebuildIndex();

                    showFeedback('textFeedback', 'success', 'Text saved as ' + title + '.txt and knowledgebase updated.');
                    loadFileList();
                    updateStats();

                    document.getElementById('textTitle').value = '';
                    document.getElementById('textContent').value = '';

                } catch (error) {
                    showFeedback('textFeedback', 'error', 'Error: ' + error.message);
                }
            });

            // Index Button
            document.getElementById('indexBtn')?.addEventListener('click', async function() {
                const apiKey = serverApiKey;
                if (!apiKey) {
                    showFeedback('indexFeedback', 'error', 'API Key is not configured on server. Please use the API Key Setup section above.');
                    return;
                }

                this.disabled = true;
                this.innerHTML = '⏳ Rebuilding...';

                try {
                    await rebuildIndex();
                    showFeedback('indexFeedback', 'success', 'Biography index rebuilt successfully!');
                    updateStats();
                } catch (error) {
                    showFeedback('indexFeedback', 'error', 'Error: ' + error.message);
                } finally {
                    this.disabled = false;
                    this.innerHTML = '🔄 Rebuild Index';
                }
            });

            async function rebuildIndex() {
                const res = await fetch('indexBio.php');
                const data = await res.json();

                if (data.status !== 'success') {
                    throw new Error(data.message || 'Index rebuild failed');
                }

                return data;
            }

            function showFeedback(elementId, type, message) {
                const el = document.getElementById(elementId);
                el.className = 'feedback ' + type;
                el.textContent = message;
                el.style.display = 'block';
            }
            
            async function loadFileList() {
                try {
                    const res = await fetch('widget_list_bio.php');
                    const data = await res.json();
                    
                    const container = document.getElementById('fileList');
                    
                    if (!data.files || data.files.length === 0) {
                        container.innerHTML = '<p style="color: rgba(255,255,255,0.5); font-size: 0.85rem;">No files in knowledgebase.</p>';
                        return;
                    }
                    
                    container.innerHTML = data.files.map(f => `
                        <div class="file-item">
                            <span class="file-name">📄 ${f.name}</span>
                            <span class="file-date">${f.size} bytes</span>
                        </div>
                    `).join('');
                    
                } catch (error) {
                    console.error('Error loading file list:', error);
                }
            }
            
            async function updateStats() {
                try {
                    // Get bio count
                    const bioRes = await fetch('widget_list_bio.php');
                    const bioData = await bioRes.json();
                    document.getElementById('bioCount').textContent = bioData.files ? bioData.files.length : 0;
                    
                    // Get log count
                    const logRes = await fetch('widget_log_stats.php');
                    if (logRes.ok) {
                        const logData = await logRes.json();
                        document.getElementById('logCount').textContent = logData.count || 0;
                    } else {
                        document.getElementById('logCount').textContent = '0';
                    }
                    
                } catch (error) {
                    console.error('Error updating stats:', error);
                }
            }
            
            // Initial load
            loadFileList();
            updateStats();
        </script>
        
        <?php endif; ?>
    </div>
</body>
</html>