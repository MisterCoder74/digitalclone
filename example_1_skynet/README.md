# Skynet Digital Clone - Marco Rossi

A corporate digital clone template featuring a floating AI chatbot that simulates Marco Rossi, an expert at Skynet. The system uses OpenAI's GPT models with a Retrieval-Augmented Generation (RAG) architecture for contextual responses.

## Features

### 🤖 AI Chat Widget
- Floating chat interface easily embeddable on any website
- Responsive design with dark glass-morphism styling
- Real-time messaging with Markdown rendering (bold, italic, code blocks, lists, headers)
- Image upload and analysis using Vision API

### 🧠 Server-side API Key Management
- `api_config.php` stores an obfuscated API key using XOR + Base64 encoding
- `proxy.php` routes all OpenAI requests server-side
- The client-side widget never receives or handles the raw API key
- API key can be configured through the admin panel with an inline encoder

### 📊 Admin Panel (`widget_admin.php`)
- Password-protected admin interface
- Upload PDF documents directly to the knowledgebase
- Extract text from PDFs via OpenAI Vision API
- Manually add text content to the knowledgebase
- Rebuild biography index from bio/ folder
- API key setup with inline encoder (updates api_config.php server-side)
- View statistics and manage knowledgebase files

### 📚 Knowledge Base (RAG System)
- Semantic search using OpenAI embeddings (`text-embedding-3-small`)
- `bio/` folder contains text documents for indexing
- `indexBio.php` generates semantic index (`biography_index.json`)
- `searchBio.php` performs similarity-based searches
- Automatic BirthDate and ZodiacalSign extraction from biography files
- `persona_details.json` is updated automatically during indexing

### 🧬 Persona & Emotional Matrix
- Biographical data defined in `persona_details.json`
- Behavioral constraints in `emotional_matrix.txt`
- Architecture details in `self_awareness.txt`
- Persona data is loaded dynamically by the widget

### 💾 Long-term Memory System
- Semantic vector memory stored in localStorage
- Configurable memory retention (max 100 entries)
- Composite scoring combining similarity and recency
- Memory persistence with server-side backup via `saveMemory.php` / `getMemory.php`

### 📝 Chat Logging & Statistics
- All conversations are logged to server via `widget_log_chat.php`
- `widget_log_stats.php` provides statistics on chat activity
- Session history can be reviewed and managed

### 🔐 Security
- API key obfuscation using XOR cipher
- All OpenAI calls proxied through server-side `proxy.php`
- Admin panel protected by password authentication
- CORS-friendly architecture (API key stays on server)

### 📄 PDF Support
- Upload PDFs up to 20MB from the admin panel
- Text extraction via OpenAI Vision API (GPT-4.1-mini)
- Extracted text automatically saved to bio/ folder
- Biography index is rebuilt after successful upload

### 🌟 Activation Page
- `activate.php` provides a branded activation flow
- Users enter their OpenAI API key to index the biography
- Automated initialization of the knowledge base

## Structure

```
example_1_skynet/
├── widget.js                # Core chat widget logic
├── widget.css               # Widget styling
├── widget_admin.php         # Admin panel interface
├── api_config.php           # API key storage (obfuscated)
├── proxy.php                # Server-side OpenAI proxy
├── index.php                # Main entry page
│
├── Core Files
│   ├── persona_details.json   # Biographical data
│   ├── emotional_matrix.txt   # Personality traits
│   ├── self_awareness.txt     # Architecture definition
│   └── biography_index.json   # Generated semantic index
│
├── bio/                     # Knowledge base documents (txt)
│   └── expertise.txt          # Sample expertise document
│
├── PHP Backend
│   ├── indexBio.php           # Biography indexer
│   ├── searchBio.php          # Semantic search
│   ├── saveMemory.php         # Memory persistence
│   ├── getMemory.php          # Memory retrieval
│   ├── widget_vupload.php     # Image upload handler
│   ├── widget_save_bio.php    # Bio file management
│   ├── widget_list_bio.php    # Bio file listing
│   ├── widget_log_chat.php    # Chat logging
│   ├── widget_log_stats.php    # Chat statistics
│   └── setup-key.php          # Key encoder utility
│
└── Additional
    └── activate.php           # Clone activation page (in project root)
```

## Setup

### 1. Prerequisites
- PHP 7.4+ with curl extension
- Web server (Apache/Nginx)
- OpenAI API key

### 2. Installation
```bash
# Place all files in your web directory
# Ensure the bio/ folder is writable
chmod 755 bio/
```

### 3. Configure API Key

**Option A: Using the Admin Panel**
1. Navigate to `widget_admin.php`
2. Enter password: `skynet_admin_2024`
3. Use the "API Key Setup" section to enter your OpenAI key
4. The key is encoded and saved automatically

**Option B: Manual Configuration**
```bash
# Encode your key using PHP
php -r "require 'api_config.php'; echo encodeKey('your-key-here');"
```

Then update `api_config.php`:
```php
$OBFUSCATED_API_KEY = 'your-encoded-key';
```

### 4. Add Knowledge Base Documents
Place `.txt` files in the `bio/` folder:
```
bio/
├── expertise.txt
├── background.txt
└── company_history.txt
```

### 5. Index the Knowledge Base
After setting up the API key, navigate to the admin panel (`widget_admin.php`) and click "Rebuild Index", or run:
```bash
curl -X POST http://your-domain/example_1_skynet/indexBio.php
```

### 6. Embed the Widget
Add this to any HTML page:
```html
<script src="widget.js"></script>
```

## API Configuration

The system uses multiple OpenAI endpoints:

| Endpoint | Purpose | Model |
|----------|---------|-------|
| `/v1/chat/completions` | Chat responses | gpt-4o-mini |
| `/v1/embeddings` | Semantic search | text-embedding-3-small |
| `/v1/chat/completions` | Vision analysis | gpt-4.1-mini |
| `/v1/files` | PDF processing | - |

All API calls are routed through `proxy.php` which handles:
- Endpoint routing based on request type
- Authentication header injection
- Error response forwarding
- CORS compatibility

## Admin Panel Usage

### Accessing the Admin Panel
Navigate to `widget_admin.php` and enter the admin password.

### Managing the Knowledge Base

1. **Upload PDF**
   - Click "Choose File" and select a PDF (max 20MB)
   - Enter a document name
   - Click "Upload & Extract Text"
   - The PDF is processed and text is added to bio/

2. **Add Text Manually**
   - Enter a document title
   - Paste or type the content
   - Click "Add to Knowledgebase"

3. **Rebuild Index**
   - Click "Rebuild Index" to regenerate the semantic index
   - BirthDate and ZodiacalSign are automatically extracted from bio files
   - `persona_details.json` is updated accordingly

### Statistics Dashboard
The admin panel shows:
- Number of knowledgebase files
- Total chat logs
- Memory entries

## Security Considerations

- **Never expose `api_config.php`**: This file contains your obfuscated API key
- **Use HTTPS in production**: The activation and admin pages transmit sensitive data
- **Change the admin password**: Update `$adminPassword` in `widget_admin.php`
- **Server permissions**: Ensure PHP files are not publicly readable

## Configuration Options

In `widget.js`, you can customize:

```javascript
const CONFIG = {
    apiEndpoint: "proxy.php?path=chat",      // Chat API endpoint
    embeddingEndpoint: "proxy.php?path=embeddings", // Embeddings endpoint
    model: "gpt-4o-mini",                    // Chat model
    visionModel: "gpt-4.1-mini",             // Vision model
    embeddingModel: "text-embedding-3-small", // Embeddings model
    embeddingDimensions: 512,               // Vector dimensions
    kbSimilarityThreshold: 0.5,            // KB search threshold
    maxVisionTokens: 28000                 // Vision token limit
};
```

## License

This is a template provided for educational and commercial use. See the main project LICENSE for details.
