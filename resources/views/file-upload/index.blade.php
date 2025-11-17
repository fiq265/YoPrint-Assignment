<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>YoPrint</title>
    <link rel="icon" href="{{ asset('img/yoprint.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body>
    <div class="container-fluid py-4 mt-5" style="max-width: 1400px;">
        <div id="message" class="alert alert-dismissible fade d-none" role="alert">
            <span id="messageText"></span>
            <button type="button" class="btn-close" onclick="hideMessage()"></button>
        </div>

        <div class="upload-section d-flex justify-content-between align-items-center">
            <label for="csvFile" class="upload-label">
                Select file/Drag and drop
            </label>
            <input type="file" id="csvFile" accept=".csv">
            <button class="btn upload-button" onclick="document.getElementById('csvFile').click()">
                Upload File
            </button>
        </div>

        <div class="table-container">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th style="width: 25%;">
                            Time
                            <span class="sort-icon">▲</span>
                        </th>
                        <th style="width: 25%;">
                            File Name
                            <span class="sort-icon">⇅</span>
                        </th>
                        <th style="width: 50%;">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="3" class="empty-state">No uploads yet</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
</body>
</html>