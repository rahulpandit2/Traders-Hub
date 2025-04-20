function uploadFile(formData) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        const progressBar = document.getElementById('uploadProgress');
        const progressDiv = document.getElementById('progressDiv');
        const cancelBtn = document.getElementById('cancelUpload');
        
        // Show progress elements
        progressDiv.style.display = 'block';
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';
        
        // Setup cancel button
        cancelBtn.onclick = () => {
            xhr.abort();
            progressDiv.style.display = 'none';
            reject(new Error('Upload cancelled'));
        };
        
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percentComplete + '%';
                progressBar.textContent = percentComplete + '%';
            }
        });
        
        xhr.addEventListener('load', () => {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    progressDiv.style.display = 'none';
                    resolve(response);
                } catch (e) {
                    reject(new Error('Invalid server response'));
                }
            } else {
                reject(new Error('Upload failed'));
            }
        });
        
        xhr.addEventListener('error', () => {
            progressDiv.style.display = 'none';
            reject(new Error('Upload failed'));
        });
        
        xhr.addEventListener('abort', () => {
            progressDiv.style.display = 'none';
            reject(new Error('Upload cancelled'));
        });
        
        xhr.open('POST', 'upload.php');
        xhr.send(formData);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('uploadForm');
    
    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(uploadForm);
        
        try {
            const response = await uploadFile(formData);
            if (response.success) {
                window.location.reload();
            } else {
                alert(response.message || 'Upload failed');
            }
        } catch (error) {
            if (error.message !== 'Upload cancelled') {
                alert(error.message);
            }
        }
    });
});