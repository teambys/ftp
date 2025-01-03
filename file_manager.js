let currentPath = '/';
let selectedFiles = new Set();
let moveSource = null;
let moveOperation = null;
let currentZoom = 1;

// File type definitions
const fileTypes = {
    // Images
    image: ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'],
    // Videos
    video: ['mp4', 'webm', 'ogg', 'mov', 'avi'],
    // Audio
    audio: ['mp3', 'wav', 'ogg', 'm4a'],
    // Code
    code: {
        javascript: ['js', 'jsx', 'json'],
        php: ['php', 'phtml'],
        css: ['css', 'scss', 'sass', 'less'],
        html: ['html', 'htm', 'xhtml'],
        python: ['py', 'pyw'],
        sql: ['sql'],
        xml: ['xml'],
        plain: ['txt', 'log', 'md']
    },
    // PDFs
    pdf: ['pdf']
};

function getFileType(fileName) {
    const ext = fileName.split('.').pop().toLowerCase();
    
    // Check each file type category
    if (fileTypes.image.includes(ext)) return 'image';
    if (fileTypes.video.includes(ext)) return 'video';
    if (fileTypes.audio.includes(ext)) return 'audio';
    if (fileTypes.pdf.includes(ext)) return 'pdf';
    
    // Check code file types
    for (const [language, extensions] of Object.entries(fileTypes.code)) {
        if (extensions.includes(ext)) return language;
    }
    
    return 'plain';
}

function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

function updateBreadcrumb() {
    const breadcrumb = document.getElementById('breadcrumb');
    breadcrumb.innerHTML = '';

    const parts = currentPath.split('/').filter(Boolean);
    let path = '';

    const homeLink = document.createElement('a');
    homeLink.href = '#';
    homeLink.textContent = 'Home';
    homeLink.addEventListener('click', (e) => {
        e.preventDefault();
        loadFileExplorer('/');
    });
    breadcrumb.appendChild(homeLink);

    parts.forEach((part, index) => {
        breadcrumb.appendChild(document.createTextNode(' > '));
        path += '/' + part;
        const link = document.createElement('a');
        link.href = '#';
        link.textContent = part;
        link.addEventListener('click', (e) => {
            e.preventDefault();
            loadFileExplorer(path);
        });
        breadcrumb.appendChild(link);
    });
}


function loadFileContent(filePath) {
    showLoading();
    
    // Add timestamp to prevent caching
    const url = `get_file_content.php?path=${encodeURIComponent(filePath)}&t=${Date.now()}`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Failed to load file content');
                });
            }
            return response.blob();
        })
        .then(blob => {
            const fileName = filePath.split('/').pop();
            const fileType = getFileType(fileName);
            const fileContent = document.getElementById('fileContent');
            const fileContentTitle = document.getElementById('fileContentTitle');
            
            // Reset all preview containers
            document.querySelectorAll('.preview-container').forEach(container => {
                container.style.display = 'none';
            });

            fileContentTitle.textContent = fileName;
            fileContent.style.display = 'block';

            // Reset zoom for image viewer
            currentZoom = 1;
            if (document.getElementById('imagePreview')) {
                document.getElementById('imagePreview').style.transform = 'scale(1)';
            }

            const objectUrl = URL.createObjectURL(blob);

            switch(true) {
                case fileType === 'image':
                    const imagePreview = document.getElementById('imagePreview');
                    imagePreview.onload = () => URL.revokeObjectURL(objectUrl);
                    imagePreview.src = objectUrl;
                    document.getElementById('imageViewer').style.display = 'block';
                    break;

                case fileType === 'video':
                    const videoPreview = document.getElementById('videoPreview');
                    videoPreview.src = objectUrl;
                    videoPreview.onended = () => URL.revokeObjectURL(objectUrl);
                    document.getElementById('videoPlayer').style.display = 'block';
                    break;

                case fileType === 'audio':
                    const audioPreview = document.getElementById('audioPreview');
                    audioPreview.src = objectUrl;
                    audioPreview.onended = () => URL.revokeObjectURL(objectUrl);
                    document.getElementById('audioPlayer').style.display = 'block';
                    break;

                case fileType === 'pdf':
                    const pdfPreview = document.getElementById('pdfPreview');
                    pdfPreview.src = objectUrl;
                    document.getElementById('pdfViewer').style.display = 'block';
                    break;

                default:
                    // Text/Code editor
                    blob.text().then(content => {
                        const codeEditor = document.getElementById('codeEditor');
                        const syntaxSelect = document.getElementById('syntaxSelect');
                        
                        // Ensure content is properly escaped for HTML
                        codeEditor.textContent = content;
                        codeEditor.className = `language-${fileType}`;
                        syntaxSelect.value = fileType;
                        
                        Prism.highlightElement(codeEditor);
                        document.getElementById('textEditor').style.display = 'block';
                    }).catch(error => {
                        console.error('Error reading text content:', error);
                        alert('Error reading file content. The file might be corrupted or in an unsupported format.');
                    });
                    break;
            }

            hideLoading();
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
            alert(error.message || 'An error occurred while loading file content. Please try again.');
        });
}

function loadFileExplorer(path, isDestination = false) {
    showLoading();
    currentPath = path;
    if (!isDestination) {
        updateBreadcrumb();
        selectedFiles.clear();
        updateButtonStates();
    }

    fetch(`list_files.php?path=${encodeURIComponent(path)}`)
        .then(response => response.json())
        .then(data => {
            const fileExplorer = isDestination ? 
                document.getElementById('destinationExplorer') : 
                document.getElementById('fileExplorer');
            fileExplorer.innerHTML = '';

            if (isDestination) {
                const upLink = document.createElement('div');
                upLink.className = 'file-item';
                upLink.innerHTML = '<i class="fas fa-level-up-alt"></i> ..';
                upLink.addEventListener('click', () => {
                    const parentPath = path.split('/').slice(0, -1).join('/') || '/';
                    loadFileExplorer(parentPath, true);
                });
                fileExplorer.appendChild(upLink);
            }

            data.forEach(item => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                
                if (!isDestination) {
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'file-checkbox';
                    checkbox.addEventListener('change', (e) => {
                        e.stopPropagation();
                        if (checkbox.checked) {
                            selectedFiles.add(item.name);
                        } else {
                            selectedFiles.delete(item.name);
                        }
                        updateButtonStates();
                    });
                    fileItem.appendChild(checkbox);
                }

                const icon = document.createElement('i');
                icon.className = item.type === 'dir' ? 'fas fa-folder' : 'fas fa-file';
                fileItem.appendChild(icon);

                const name = document.createElement('span');
                name.textContent = item.name;
                fileItem.appendChild(name);

                fileItem.addEventListener('click', (e) => {
                    if (e.target.type !== 'checkbox') {
                        if (item.type === 'dir') {
                            loadFileExplorer(path + '/' + item.name, isDestination);
                        } else if (!isDestination) {
                            loadFileContent(path + '/' + item.name);
                        }
                    }
                });

                fileExplorer.appendChild(fileItem);
            });

            hideLoading();
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
            alert('An error occurred while loading files. Please try again.');
        });
}

function saveFileContent() {
    const filePath = currentPath + '/' + document.getElementById('fileContentTitle').textContent;
    const content = document.getElementById('codeEditor').textContent;

    showLoading();
    fetch('save_file_content.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `path=${encodeURIComponent(filePath)}&content=${encodeURIComponent(content)}`
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            alert('File saved successfully!');
            loadFileExplorer(currentPath);
        } else {
            alert('Error saving file: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoading();
        alert('An error occurred while saving the file. Please try again.');
    });
}

function uploadFiles(files) {
    showLoading();
    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    formData.append('path', currentPath);

    fetch('upload_files.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            alert('Files uploaded successfully!');
            loadFileExplorer(currentPath);
        } else {
            alert('Error uploading files: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoading();
        alert('An error occurred while uploading files. Please try again.');
    });
}

function createNewFolder() {
    const folderName = prompt('Enter the new folder name:');
    if (folderName) {
        showLoading();
        fetch('create_folder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `path=${encodeURIComponent(currentPath)}&name=${encodeURIComponent(folderName)}`
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                alert('Folder created successfully!');
                loadFileExplorer(currentPath);
            } else {
                alert('Error creating folder: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
            alert('An error occurred while creating the folder. Please try again.');
        });
    }
}

function createNewFile() {
    const fileName = prompt('Enter the new file name:');
    if (fileName) {
        showLoading();
        fetch('create_file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `path=${encodeURIComponent(currentPath)}&name=${encodeURIComponent(fileName)}`
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                alert('File created successfully!');
                loadFileExplorer(currentPath);
            } else {
                alert('Error creating file: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
            alert('An error occurred while creating the file. Please try again.');
        });
    }
}

function showMoveModal(operation) {
    if (selectedFiles.size === 0) {
        alert('Please select files to ' + operation);
        return;
    }

    moveOperation = operation;
    moveSource = currentPath;
    const moveModal = document.getElementById('moveModal');
    const title = document.getElementById('moveModalTitle');
    const confirmMoveBtn = document.getElementById('confirmMoveBtn');
    const confirmCopyBtn = document.getElementById('confirmCopyBtn');
    
    title.textContent = operation === 'move' ? 'Move Files' : 'Copy Files';
    confirmMoveBtn.style.display = operation === 'move' ? 'block' : 'none';
    confirmCopyBtn.style.display = operation === 'copy' ? 'block' : 'none';
    
    moveModal.style.display = 'block';
    loadFileExplorer('/', true);
}

function moveOrCopyFiles() {
    showLoading();
    fetch('move_files.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            sourcePath: moveSource,
            targetPath: currentPath,
            files: Array.from(selectedFiles),
            operation: moveOperation
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            alert('Files ' + moveOperation + 'd successfully!');
            document.getElementById('moveModal').style.display = 'none';
            loadFileExplorer(currentPath);
        } else {
            alert('Error ' + moveOperation + 'ing files: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoading();
        alert('An error occurred while ' + moveOperation + 'ing files. Please try again.');
    });
}

function deleteSelectedFiles() {
    if (selectedFiles.size === 0) {
        alert('Please select files or folders to delete.');
        return;
    }

    if (confirm('Are you sure you want to delete the selected files/folders?')) {
        showLoading();
        fetch('delete_files.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                path: currentPath,
                files: Array.from(selectedFiles)
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                alert('Files/folders deleted successfully!');
                loadFileExplorer(currentPath);
            } else {
                alert('Error deleting files/folders: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
            alert('An error occurred while deleting files/folders. Please try again.');
        });
    }
}

function downloadSelectedFiles() {
    if (selectedFiles.size === 0) {
        alert('Please select files or folders to download.');
        return;
    }

    showLoading();
    fetch('create_zip.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            path: currentPath,
            files: Array.from(selectedFiles)
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            window.location.href = data.zipUrl;
        } else {
            alert('Error creating zip file: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoading();
        alert('An error occurred while creating the zip file. Please try again.');
    });
}

function updateButtonStates() {
    const deleteBtn = document.getElementById('deleteBtn');
    const downloadBtn = document.getElementById('downloadBtn');
    const moveBtn = document.getElementById('moveBtn');
    const copyBtn = document.getElementById('copyBtn');
    
    const hasSelection = selectedFiles.size > 0;
    deleteBtn.disabled = !hasSelection;
    downloadBtn.disabled = !hasSelection;
    moveBtn.disabled = !hasSelection;
    copyBtn.disabled = !hasSelection;
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        showLoading();
        fetch('logout.php')
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error logging out: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
            alert('An error occurred while logging out. Please try again.');
        });
    }
}

// Image zoom controls
function handleImageZoom(action) {
    const img = document.getElementById('imagePreview');
    
    switch(action) {
        case 'in':
            currentZoom *= 1.2;
            break;
        case 'out':
            currentZoom /= 1.2;
            break;
        case 'reset':
            currentZoom = 1;
            break;
    }
    
    // Limit zoom levels
    currentZoom = Math.min(Math.max(0.1, currentZoom), 5);
    img.style.transform = `scale(${currentZoom})`;
}


// Event Listeners
document.getElementById('uploadBtn').addEventListener('click', () => {
    document.getElementById('fileInput').click();
});

document.getElementById('fileInput').addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        uploadFiles(e.target.files);
    }
});

document.getElementById('newFolderBtn').addEventListener('click', createNewFolder);
document.getElementById('newFileBtn').addEventListener('click', createNewFile);
document.getElementById('moveBtn').addEventListener('click', () => showMoveModal('move'));
document.getElementById('copyBtn').addEventListener('click', () => showMoveModal('copy'));
document.getElementById('deleteBtn').addEventListener('click', deleteSelectedFiles);
document.getElementById('downloadBtn').addEventListener('click', downloadSelectedFiles);
document.getElementById('logoutBtn').addEventListener('click', logout);

document.getElementById('saveFileBtn').addEventListener('click', saveFileContent);
document.getElementById('syntaxSelect').addEventListener('change', (e) => {
    const codeEditor = document.getElementById('codeEditor');
    codeEditor.className = `language-${e.target.value}`;
    Prism.highlightElement(codeEditor);
});

document.getElementById('confirmMoveBtn').addEventListener('click', moveOrCopyFiles);
document.getElementById('confirmCopyBtn').addEventListener('click', moveOrCopyFiles);
document.getElementById('cancelMoveBtn').addEventListener('click', () => {
    document.getElementById('moveModal').style.display = 'none';
});

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    const fileContent = document.getElementById('fileContent');
    const moveModal = document.getElementById('moveModal');
    if (e.target === fileContent) {
        fileContent.style.display = 'none';
    }
    if (e.target === moveModal) {
        moveModal.style.display = 'none';
    }
});

// Close modals with close button
document.querySelectorAll('.close').forEach(closeBtn => {
    closeBtn.addEventListener('click', () => {
        closeBtn.closest('.modal').style.display = 'none';
    });
});

// Initial load
loadFileExplorer('/');

// Additional Event Listeners
document.getElementById('zoomInBtn')?.addEventListener('click', () => handleImageZoom('in'));
document.getElementById('zoomOutBtn')?.addEventListener('click', () => handleImageZoom('out'));
document.getElementById('resetZoomBtn')?.addEventListener('click', () => handleImageZoom('reset'));

// Make code editor content editable
document.getElementById('codeEditor')?.addEventListener('input', function() {
    Prism.highlightElement(this);
});

// Prevent tab key from moving focus out of code editor
document.getElementById('codeEditor')?.addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
        e.preventDefault();
        const start = this.selectionStart;
        const end = this.selectionEnd;
        this.textContent = this.textContent.substring(0, start) + 
                          '    ' + 
                          this.textContent.substring(end);
        this.selectionStart = this.selectionEnd = start + 4;
        Prism.highlightElement(this);
    }
});

