# Skynet Digital Clone - Marco Rossi

This is a corporate digital clone template for Marco Rossi, an expert at Skynet.

## Features
- **Floating Chat Widget**: A self-contained chat interface that can be easily embedded.
- **RAG System**: Uses OpenAI embeddings for semantic search in the Knowledge Base and long-term memory.
- **Persona & Emotion**: Defined via JSON and text files to simulate a consistent personality.
- **KB Management**: Automated indexing of text files in the `bio/` folder.

## Structure
- `widget.js` & `widget.css`: Core widget logic and styling.
- `persona_details.json`: Marco Rossi's biographical data.
- `emotional_matrix.txt`: Behavioral constraints and personality traits.
- `self_awareness.txt`: Architecture details.
- `bio/`: Folder containing Knowledge Base documents (txt).
- `indexBio.php`: Script to generate the semantic index (`biography_index.json`).
- `searchBio.php`: Script for semantic search in the KB.
- `saveMemory.php` & `getMemory.php`: Scripts for server-side memory persistence.

## Setup
1. Place your text documents in the `bio/` folder.
2. Configure your OpenAI API Key in `api_config.php`. You can use the included `setup-key.php` utility to encode your key.
3. Index the Knowledge Base by calling the indexing script:
   `indexBio.php`
4. Embed the widget in any page by including `widget.js`.

## Security
- API Key is stored only on the server in `api_config.php` (obfuscated).
- All OpenAI calls from the widget are routed through `proxy.php`.
- The client-side widget never has access to the raw API key.
