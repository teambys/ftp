<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced FTP File Manager</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism-tomorrow.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-markup.min.js"></script>
</head>
<body class="dark-theme">
    <div class="container">
        <header>
            <div class="header-content">
                <h1><i class="fas fa-terminal"></i> Enhanced FTP File Manager</h1>
                <div class="button-groups">
                    <div class="button-group">
                        <button id="uploadBtn" class="btn primary"><i class="fas fa-upload"></i> Upload</button>
                        <button id="newFolderBtn" class="btn primary"><i class="fas fa-folder-plus"></i> New Folder</button>
                        <button id="newFileBtn" class="btn primary"><i class="fas fa-file-plus"></i> New File</button>
                    </div>
                    <div class="button-group">
                        <button id="copyBtn" class="btn secondary" disabled><i class="fas fa-copy"></i> Copy</button>
                        <button id="moveBtn" class="btn secondary" disabled><i class="fas fa-arrows-alt"></i> Move</button>
                        <button id="deleteBtn" class="btn danger" disabled><i class="fas fa-trash"></i> Delete</button>
                        <button id="downloadBtn" class="btn secondary" disabled><i class="fas fa-download"></i> Download</button>
                    </div>
                    <div class="button-group">
                        <button id="logoutBtn" class="btn warning"><i class="fas fa-sign-out-alt"></i> Logout</button>
                    </div>
                </div>
            </div>
        </header>
        <main>
            <div id="breadcrumb" class="terminal-breadcrumb"></div>
            <div id="fileExplorer" class="terminal-explorer"></div>
        </main>
        
        <!-- Enhanced File Content Modal -->
        <div id="fileContent" class="modal">
            <div class="modal-content terminal-modal">
                <div class="modal-header">
                    <div class="terminal-title">
                        <i class="fas fa-terminal"></i>
                        <span id="fileContentTitle"></span>
                    </div>
                    <div class="terminal-controls">
                        <span class="terminal-button minimize">─</span>
                        <span class="terminal-button maximize">□</span>
                        <span class="terminal-button close">×</span>
                    </div>
                </div>
                <div class="modal-body">
                    <!-- Code Editor -->
                    <div id="textEditor" class="preview-container terminal-editor">
                        <div class="editor-toolbar">
                            <select id="syntaxSelect" class="terminal-select">
                                <option value="plain">Plain Text</option>
                                <option value="javascript">JavaScript</option>
                                <option value="php">PHP</option>
                                <option value="css">CSS</option>
                                <option value="html">HTML</option>
                                <option value="xml">XML</option>
                                <option value="sql">SQL</option>
                            </select>
                            <button id="saveFileBtn" class="btn primary">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>
                        <div class="code-container">
                            <pre><code id="codeEditor" class="language-plain" contenteditable="true" spellcheck="false"></code></pre>
                        </div>
                    </div>
                    
                    <!-- Image Viewer -->
                    <div id="imageViewer" class="preview-container">
                        <div class="image-controls">
                            <button id="zoomInBtn" class="btn secondary">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button id="zoomOutBtn" class="btn secondary">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button id="resetZoomBtn" class="btn secondary">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                        <div class="image-wrapper">
                            <img id="imagePreview" src="" alt="Preview">
                        </div>
                    </div>
                    
                    <!-- Video Player -->
                    <div id="videoPlayer" class="preview-container">
                        <video id="videoPreview" controls>
                            Your browser does not support the video tag.
                        </video>
                    </div>

                    <!-- Audio Player -->
                    <div id="audioPlayer" class="preview-container">
                        <audio id="audioPreview" controls>
                            Your browser does not support the audio tag.
                        </audio>
                    </div>

                    <!-- PDF Viewer -->
                    <div id="pdfViewer" class="preview-container">
                        <iframe id="pdfPreview" src="" frameborder="0"></iframe>
                    </div>

                    <!-- Error Display -->
                    <div id="errorDisplay" class="preview-container error-container">
                        <div class="error-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="error-message"></div>
                        <button class="btn secondary" onclick="document.getElementById('fileContent').style.display='none'">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Move/Copy Modal -->
        <div id="moveModal" class="modal">
            <div class="modal-content terminal-modal">
                <div class="modal-header">
                    <div class="terminal-title">
                        <i class="fas fa-terminal"></i>
                        <span id="moveModalTitle">Move/Copy Files</span>
                    </div>
                    <div class="terminal-controls">
                        <span class="terminal-button close">×</span>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="destinationExplorer" class="terminal-explorer"></div>
                    <div class="modal-footer">
                        <button id="confirmMoveBtn" class="btn primary">Move Here</button>
                        <button id="confirmCopyBtn" class="btn primary">Copy Here</button>
                        <button id="cancelMoveBtn" class="btn secondary">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="file" id="fileInput" style="display: none;" multiple>
    <div id="loadingOverlay" class="loading-overlay">
        <div class="terminal-spinner"></div>
    </div>
    <script src="file_manager.js"></script>
</body>
</html>

