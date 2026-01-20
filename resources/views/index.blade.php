<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Linkly-URL Shortener</title>
    @vite('resources/css/index.css')
</head>

<body>
    <div class="wrapper">
        <div class="navbar">
            <div class="navbar-title">
                Linkly
            </div>
        </div>
        <div class="hero-section">
            <h2> Shorten Your Loooong Links :)
            </h2>
            <p>Linkly is an efficient and easy-to-use URL shortening service that streamlines your online
                experience.</p>
            <div class="button">
                <img src="{{ asset('images/link.svg') }}" alt="icon">
                <input type="text" placeholder="Enter the link here" id="longUrl">
                <button id="shortenBtn">Shorten Now!</button>
            </div>

            <div id="result" class="result-area"
                style="margin-top: 1.5rem; display: flex; align-items: center; width: 80%">
                <p style="text-align: left; width: auto; margin-right: 20px;margin-top: 24px;">Your short link:</p>
                <a href="" id="shortUrl" style="color: white; text-align: center;"></a>
                <p id="errorMsg" style="color: #e74c3c; margin-top: 0.8rem; display: none;"></p>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('shortenBtn').addEventListener('click', async function() {
            const longUrlInput = document.getElementById('longUrl');
            const longUrl = longUrlInput.value.trim();
            console.log(longUrl);
            const resultArea = document.getElementById('result');
            const shortUrlField = document.getElementById('shortUrl');
            const errorMsg = document.getElementById('errorMsg');

            // Reset previous states
            errorMsg.style.display = 'none';
            resultArea.style.display = 'none';
            shortUrlField.value = '';

            if (!longUrl) {
                showError('Please enter a URL!');
                return;
            }

            if (!isValidUrl(longUrl)) {
                showError('Please enter a valid URL (http:// or https://)');
                return;
            }

            try {
                this.disabled = true;
                this.textContent = 'Shortening...';

                const response = await fetch('/api/urls', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        // 'X-CSRF-TOKEN': '{{ csrf_token() }}'   ← add this if you use Laravel CSRF (recommended!)
                    },
                    body: JSON.stringify({
                        url: longUrl
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong');
                }

                // Success!
                shortUrlField.innerHTML = data.short_url;
                shortUrlField.href = data.short_url;
                resultArea.style.display = 'flex';

            } catch (error) {
                showError(error.message || 'Failed to shorten URL. Try again!');
            } finally {
                this.disabled = false;
                this.textContent = 'Shorten Now!';
            }

        })

        function showError(message) {
            const errorEl = document.getElementById('errorMsg');
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
    </script>
</body>

</html>
