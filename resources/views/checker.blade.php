<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Webpage Text Checker</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: auto; padding: 20px; line-height: 1.6; }
        h1,h2,h3 { color: #333; }
        form { background: #f4f4f4; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        input[type="url"], input[type="file"], input[type="text"], input[type="password"], select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #007BFF; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .auth-section { border: 1px dashed #ccc; padding: 15px; margin-top: 15px; border-radius: 5px; }
        .error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: .75rem 1.25rem; margin-bottom: 1rem; border-radius: .25rem; }
        .results-container { margin-top: 30px; }
        .result-box { margin-bottom: 20px; }
        ul { padding-left: 20px; list-style-position: inside; }
        .checkbox-label { display: block; margin-top: 15px; font-weight: normal; }
        .accordion { background-color: #eee; color: #444; cursor: pointer; padding: 12px 18px; width: 100%; border: none; text-align: left; outline: none; font-size: 1rem; transition: 0.4s; border-radius: 5px; margin-top: 5px; display: flex; justify-content: space-between; align-items: center; }
        .accordion.active, .accordion:hover { background-color: #ccc; }
        .accordion:after { content: '\002B'; color: #777; font-weight: bold; font-size: 1.2rem; }
        .accordion.active:after { content: "\2212"; }
        .panel { padding: 0 18px; background-color: white; max-height: 0; overflow: hidden; transition: max-height 0.2s ease-out; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
        .panel ul { margin-top: 10px; margin-bottom: 10px; }
        .found { background-color: #e9f7ef; border-color: #5cb85c; }
        .missing { background-color: #f8d7da; border-color: #d9534f; }

         /* --- START OF LOADING UI CSS --- */
        #loading-overlay {
            position: fixed; /* Cover the entire screen */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7); /* Semi-transparent background */
            display: none; /* Hidden by default */
            justify-content: center;
            align-items: center;
            z-index: 1000;
            color: white;
            flex-direction: column;
        }

        .spinner {
            border: 8px solid #f3f3f3; /* Light grey */
            border-top: 8px solid #90d; /* Blue */
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1.5s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* --- END OF LOADING UI CSS --- */
    </style>
</head>
<body>
    <div id="loading-overlay">
        <div class="spinner"></div>
        <p>Checking page, please wait...</p>
    </div>
    <h2>Webpage Text Checker</h2>
    <p>Enter a URL and a CSV file to check for text on the desktop and/or mobile version of a site.</p>

    @if(session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif
    
    @if ($errors->any())
        <div class="error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="checker-form" method="POST" action="{{ route('checker.check') }}" enctype="multipart/form-data">
        @csrf
        <label for="url">Webpage URL:</label>
        <input type="url" id="url" name="url" placeholder="https://stg-global-brand.dw-sites-intl.com" value="{{ old('url', $submitted_url ?? '') }}" required>
        
        <label for="csv_file">CSV File <small>(one sentence per row)</small>:</label> <br>
        <label for="csv_file"> <small> <a href="#" id="downloadLink" onclick="downloadFile()">Download Sample File</a> </small> </label>
        <input type="file" id="csv_file" name="csv_file" accept=".csv,text/csv" required>
        
        <label for="device_type">Check Site Version:</label>
        <select id="device_type" name="device_type">
            <option value="both" @selected(old('device_type') == 'both')>Both Desktop and Mobile</option>
            <option value="desktop" @selected(old('device_type') == 'desktop')>Desktop Only</option>
            <option value="mobile" @selected(old('device_type') == 'mobile')>Mobile Only</option>
        </select>
        
        <label class="checkbox-label">
            <input type="checkbox" name="exact_match" value="true" @checked(old('exact_match'))> Perform exact, case-sensitive search
        </label>
        
        <div class="auth-section">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="storefront" value="{{ old('username') }}">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="storefront password">
        </div>
        <br>
        <button type="submit">Check Text</button>
    </form>
    
    @isset($results)
    <div class="results-container">
        <h3>Results for: <a href="{{ $submitted_url }}" target="_blank">{{ $submitted_url }}</a></h3>
        
        @isset($results['desktop'])
        <div class="result-box">
            <h4>🖥️ Desktop Results</h4>
            <button class="accordion found">✅ Found ({{ count($results['desktop']['found']) }})</button>
            <div class="panel">
                <ul>
                    @forelse ($results['desktop']['found'] as $item)
                        <li>{{ $item }}</li>
                    @empty
                        <li>None</li>
                    @endforelse
                </ul>
            </div>
            <button class="accordion missing">❌ Missing ({{ count($results['desktop']['missing']) }})</button>
            <div class="panel">
                <ul>
                    @forelse ($results['desktop']['missing'] as $item)
                        <li>{{ $item }}</li>
                    @empty
                        <li>None</li>
                    @endforelse
                </ul>
            </div>
        </div>
        @endisset
        
        @isset($results['mobile'])
        <div class="result-box">
            <h4>📱 Mobile Results</h4>
            <button class="accordion found">✅ Found ({{ count($results['mobile']['found']) }})</button>
            <div class="panel">
                <ul>
                    @forelse ($results['mobile']['found'] as $item)
                        <li>{{ $item }}</li>
                    @empty
                        <li>None</li>
                    @endforelse
                </ul>
            </div>
            <button class="accordion missing">❌ Missing ({{ count($results['mobile']['missing']) }})</button>
            <div class="panel">
                <ul>
                    @forelse ($results['mobile']['missing'] as $item)
                        <li>{{ $item }}</li>
                    @empty
                        <li>None</li>
                    @endforelse
                </ul>
            </div>
        </div>
        @endisset
    </div>
    @endisset

    <script>
        var acc = document.getElementsByClassName("accordion");
        for (var i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function () {
                this.classList.toggle("active");
                var panel = this.nextElementSibling;
                if (panel.style.maxHeight) {
                    panel.style.maxHeight = null;
                } else {
                    panel.style.maxHeight = panel.scrollHeight + "px";
                }
            });
        }

    // --- START OF LOADING UI JAVASCRIPT ---
        const form = document.getElementById('checker-form');
        const loadingOverlay = document.getElementById('loading-overlay');

        // When the form is submitted...
        form.addEventListener('submit', function() {
            // ...display the loading overlay.
            loadingOverlay.style.display = 'flex';
        });
        // --- END OF LOADING UI JAVASCRIPT ---

        async function downloadFile() {
            // URL of the file to download (your Google Sheets CSV export link)
            const fileUrl = 'https://docs.google.com/spreadsheets/d/1KN1CgIl7zV_TcWxcxrvyOciKWNT8mm8KutwPh_362FA/export?format=csv';
            
            try {
                // Fetch the file
                const response = await fetch(fileUrl);
                if (!response.ok) {
                    throw new Error('Failed to fetch the file');
                }
                
                // Get the file as a blob
                const blob = await response.blob();
                
                // Generate a unique filename using a timestamp
                const timestamp = new Date().toISOString().replace(/[:.]/g, '-'); // e.g., 2025-09-02T12-25-00-000Z
                const uniqueFileName = `sample_file_${timestamp}.csv`;
                
                // Create a temporary link to trigger the download
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = uniqueFileName; // Set the unique filename
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link); // Clean up
                URL.revokeObjectURL(link.href); // Free memory
            } catch (error) {
                console.error('Error downloading file:', error);
                alert('Failed to download the file. Please try again.');
            }
        }
    </script>
</body>
</html>