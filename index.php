<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historical Dilbert Viewer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .loading-spinner {
            border: 8px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: #007bff;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #comic-image {
            max-width: 100%;
            height: auto;
        }
        #navigation {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
        }
        #prev-button, #next-button {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            color: white;
            background-color: #3b82f6;
            transition: background-color 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
        }
        #prev-button:hover, #next-button:hover {
            background-color: #2563eb;
        }
        #prev-button:disabled, #next-button:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .container {
            max-width: 90%;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem;
            margin-top: 1rem;
            margin-bottom: 1rem;
        }
        .flex-col {
            flex-direction: column;
            display: flex;
            align-items: center;
        }
        #comic-date {
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.5rem;
        }
        #search-form {
            margin-bottom: 1rem;
            display: flex;
            justify-content: center;
        }
        #search-input {
            padding: 0.5rem;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
            margin-right: 0.5rem;
            width: 150px;
        }
        #search-button {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            background-color: #3b82f6;
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #search-button:hover {
            background-color: #2563eb;
        }
        #search-button:disabled{
            background-color: #9ca3af;
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="loading" id="loading">
        <div class="loading-spinner"></div>
        <span class="ml-3 text-gray-700">Loading...</span>
    </div>

    <div class="container">
        <h1 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Historical Dilbert Comic Viewer</h1>

        <form id="search-form" class="flex justify-center">
            <input type="text" id="search-input" placeholder="Search by date (YYYY-MM-DD)" class="rounded-md">
            <button id="search-button" type="submit" class="bg-blue-500 text-white font-medium py-2 px-4 rounded-md hover:bg-blue-700">Search</button>
        </form>

        <div id="comic-container" class="max-w-full flex justify-center mb-4 flex-col items-center">
            <img id="comic-image" src="" alt="Comic" class="rounded-lg shadow-md" style="display: none;">
            <p id="comic-date" style="display: none;"></p>
        </div>

        <div id="navigation" class="flex justify-center" style="display: none;">
            <button id="prev-button" class="prev-button" disabled>
                <span class="material-icons mr-1">arrow_back</span> Previous
            </button>
            <button id="next-button" class="next-button">
                Next <span class="material-icons ml-1">arrow_forward</span>
            </button>
        </div>
        <p id="error-message" class="text-red-500 mt-4 text-center" style="display: none;"></p>
    </div>

    <script>
        const comicImage = document.getElementById('comic-image');
        const prevButton = document.getElementById('prev-button');
        const nextButton = document.getElementById('next-button');
        const loadingIndicator = document.getElementById('loading');
        const navigation = document.getElementById('navigation');
        const errorMessageDisplay = document.getElementById('error-message');
        const comicDateDisplay = document.getElementById('comic-date');
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const searchButton = document.getElementById('search-button');


        let currentComicIndex = 0;
        let comicFiles = [];
        let comicRoot = '/comics/';

        async function getComicList() {
            try {
                const response = await fetch('get_comics.php?root=' + encodeURIComponent(comicRoot));
                if (!response.ok) {
                    throw new Error(`Failed to fetch comic list: ${response.status}`);
                }
                const data = await response.json();
                if (data.error) {
                    throw new Error(`Server error: ${data.error}`);
                }
                comicFiles = data.files;
                if (comicFiles.length === 0) {
                    throw new Error('No comics found in the specified directory.');
                }
                comicFiles.sort();
                return comicFiles;
            } catch (error) {
                console.error("Error fetching comic list:", error);
                errorMessage('Failed to load comic list. Please check the console for details.');
                return [];
            }
        }

        async function loadComic(index) {
            if (index < 0 || index >= comicFiles.length) {
                return;
            }

            loadingIndicator.style.display = 'flex';
            comicImage.style.display = 'none';
            errorMessageDisplay.style.display = 'none';
            prevButton.disabled = index === 0;
            nextButton.disabled = index === comicFiles.length - 1;
            comicDateDisplay.style.display = 'none';


            currentComicIndex = index;
            localStorage.setItem('lastViewedIndex', index); // Save progress here
            const comicFile = comicFiles[index];
            const comicPath = comicRoot + comicFile;

            try {
                const imgResponse = await fetch(comicPath, { method: 'HEAD' });
                if (!imgResponse.ok) {
                    throw new Error(`Comic image not found: ${comicPath}`);
                }
                comicImage.src = comicPath;
                comicImage.onload = () => {
                    loadingIndicator.style.display = 'none';
                    comicImage.style.display = 'block';
                    navigation.style.display = 'flex';

                    const datePart = comicFile.match(/^\d{4}\/(\d{4}-\d{2}-\d{2})_/);
                    console.log("Filename:", comicFile);
                    console.log("datePart:", datePart);

                    if (datePart && datePart[1]) {
                        const formattedDate = formatDate(datePart[1]);
                        comicDateDisplay.textContent = formattedDate;
                        comicDateDisplay.style.display = 'block';
                    } else {
                        comicDateDisplay.textContent = '';
                        comicDateDisplay.style.display = 'none';
                    }
                };
                comicImage.onerror = () => {
                    loadingIndicator.style.display = 'none';
                    errorMessage(`Failed to load comic: ${comicPath}`);
                };

            } catch (error) {
                console.error("Error loading comic:", error);
                loadingIndicator.style.display = 'none';
                errorMessage(`Failed to load comic.  Check console for error details.`);
            }
        }

        function errorMessage(message) {
            errorMessageDisplay.textContent = message;
            errorMessageDisplay.style.display = 'block';
            navigation.style.display = 'none';
        }

        function formatDate(dateString) {
            try {
                const [year, month, day] = dateString.split('-');
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(year, month - 1, day).toLocaleDateString(undefined, options);
            } catch (error) {
                console.error("Error formatting date", error);
                return "Invalid Date";
            }
        }

        prevButton.addEventListener('click', () => {
            if (currentComicIndex > 0) {
                loadComic(currentComicIndex - 1);
            }
        });

        nextButton.addEventListener('click', () => {
            if (currentComicIndex < comicFiles.length - 1) {
                loadComic(currentComicIndex + 1);
            }
        });

        searchForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const searchTerm = searchInput.value;
            if (searchTerm) {
                searchComic(searchTerm);
            }
        });

        function searchComic(dateString) {
            const targetDate = dateString;
            let foundIndex = -1;

            for (let i = 0; i < comicFiles.length; i++) {
                const filename = comicFiles[i];
                const fileDate = filename.match(/^\d{4}\/(\d{4}-\d{2}-\d{2})_/);
                if (fileDate && fileDate[1] === targetDate) {
                    foundIndex = i;
                    break;
                }
            }

            if (foundIndex !== -1) {
                loadComic(foundIndex);
            } else {
                errorMessage(`Comic for date ${targetDate} not found.`);
            }
        }


        async function initialize() {
            try {
                const files = await getComicList();
                if (files.length > 0) {
                    const lastViewedIndex = localStorage.getItem('lastViewedIndex');
                    if (lastViewedIndex !== null) {
                        currentComicIndex = parseInt(lastViewedIndex, 10);
                        if(currentComicIndex < 0 || currentComicIndex >= comicFiles.length){
                            currentComicIndex = 0;
                            localStorage.setItem('lastViewedIndex', 0);
                        }
                    }
                    loadComic(currentComicIndex);
                } else {
                    loadingIndicator.style.display = 'none';
                    errorMessage('No comics found. Please check your comics directory.');
                }
            } catch (error) {
                loadingIndicator.style.display = 'none';
                console.error("Initialization error:", error);
                errorMessage('Failed to initialize the comic viewer. Check console for details.');
            }
        }

        initialize();
    </script>
</body>
</html>

