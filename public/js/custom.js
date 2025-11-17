$(document).ready(function() {
            
    axios.defaults.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

    let pollInterval = null;

    $('#csvFile').on('change', function(e) {
        if (this.files.length > 0) {
            uploadFile();
        }
    });

    async function uploadFile() {
        const fileInput = $('#csvFile')[0];
        
        if (!fileInput.files.length) {
            showMessage('Please select a file first.', 'danger');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);

        try {
            showMessage('Uploading...', 'info');

            const response = await axios.post('/file/store', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            showMessage(response.data.message, 'success');
            $('#csvFile').val('');
            loadUploads();
        } catch (error) {
            const errorMessage = error.response?.data?.message || 'Upload failed. Please try again.';
            showMessage(errorMessage, 'danger');
        }
    }

    function showMessage(message, type) {
        const $messageDiv = $('#message');
        const $messageText = $('#messageText');
        
        $messageText.text(message);
        $messageDiv.attr('class', `alert alert-${type} alert-dismissible fade show`);
        
        setTimeout(() => {
            hideMessage();
        }, 5000);
    }

    function hideMessage() {
        const $messageDiv = $('#message');
        $messageDiv.removeClass('show');
        setTimeout(() => {
            $messageDiv.addClass('d-none');
        }, 150);
    }

    async function loadUploads() {
        try {
            const response = await axios.get('/file/list');
            displayUploads(response.data.data);
        } catch (error) {
            console.error('Failed to load uploads:', error);
        }
    }

    function getRelativeTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'just now';
        if (diffMins === 1) return '1 minute ago';
        if (diffMins < 60) return `${diffMins} minutes ago`;
        if (diffHours === 1) return '1 hour ago';
        if (diffHours < 24) return `${diffHours} hours ago`;
        if (diffDays === 1) return '1 day ago';
        return `${diffDays} days ago`;
    }

    function displayUploads(uploads) {
        const $tableBody = $('#tableBody');
        
        if (!uploads || uploads.length === 0) {
            $tableBody.html('<tr><td colspan="3" class="empty-state">No uploads yet</td></tr>');
            return;
        }

        let html = '';

        $.each(uploads, function(index, upload) {
            const uploadDate = new Date(upload.uploaded_at);
            const timeMain = uploadDate.toLocaleString('en-US', { 
                month: '2-digit', 
                day: '2-digit', 
                year: '2-digit',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true 
            }).replace(',', '');
            
            const timeRelative = `(${getRelativeTime(upload.uploaded_at)})`;

            let statusText = upload.status;
            if (upload.status === 'processing') {
                if (upload.total_rows && upload.total_rows > 0) {
                    const percentage = Math.round((upload.processed_rows / upload.total_rows) * 100);
                    statusText = `Processing (${percentage}%)`;
                } else {
                    statusText = `Processing (${upload.processed_rows} rows)`;
                }
            } else if (upload.status === 'completed') {
                statusText = `Completed`;
            } else if (upload.status === 'failed') {
                statusText = `Failed${upload.error_message ? ' - ' + upload.error_message : ''}`;
            }

            html += `
                <tr>
                    <td>
                        <div class="time-main">${timeMain}</div>
                        <div class="time-sub">${timeRelative}</div>
                    </td>
                    <td>${upload.filename}</td>
                    <td>${statusText}</td>
                </tr>
            `;
        });

        $tableBody.html(html);
    }

    function startPolling() {
        pollInterval = setInterval(loadUploads, 2000);
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    loadUploads();
    startPolling();

    $(document).on('visibilitychange', function() {
        if (document.hidden) {
            stopPolling();
        } else {
            loadUploads();
            startPolling();
        }
    });
});