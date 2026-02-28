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
            margin: 0;
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
            width: 100%;
            max-width: 100%;
            height: auto;
            object-fit: contain;
        }
        #comic-container {
            width: 100%;
        }
        #comic-panels {
            display: none;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x mandatory;
        }
        #comic-panels-track {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }
        .comic-panel {
            flex: 0 0 100%;
            scroll-snap-align: start;
            display: flex;
            justify-content: center;
        }
        .comic-panel img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        }
        #navigation {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
            width: 100%;
            flex-wrap: wrap;
        }
        #prev-button, #next-button {
            padding: 0.5rem 1rem;
            min-height: 44px;
            border-radius: 0.375rem;
            font-weight: 500;
            color: white;
            background-color: #3b82f6;
            transition: background-color 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
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
            width: min(980px, 100%);
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
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        #search-input {
            padding: 0.5rem;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
            width: min(100%, 280px);
            min-height: 44px;
        }
        #search-button {
            padding: 0.5rem 1rem;
            min-height: 44px;
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
        @media (max-width: 640px) {
            .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
                margin-top: 0.75rem;
                margin-bottom: 0.75rem;
            }
            #search-form {
                width: 100%;
                align-items: stretch;
            }
            #search-input,
            #search-button,
            #prev-button,
            #next-button {
                width: 100%;
            }
            #navigation {
                gap: 0.5rem;
            }
            #comic-image {
                max-height: 72vh;
            }
            #comic-panels {
                max-height: 72vh;
            }
            #comic-panels-track {
                gap: 0.5rem;
            }
        }
        @media (max-width: 400px) {
            .container {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            #search-form {
                gap: 0.375rem;
                margin-bottom: 0.75rem;
            }
            #comic-date {
                font-size: 0.8125rem;
            }
            #comic-image {
                max-height: 68vh;
            }
            #comic-panels {
                max-height: 68vh;
            }
        }
        @media (orientation: landscape) and (max-height: 520px) {
            .container {
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
            }
            #viewer-title {
                font-size: 1.125rem;
                margin-bottom: 0.5rem;
            }
            #search-form {
                margin-bottom: 0.5rem;
            }
            #comic-image {
                max-height: 58vh;
            }
            #comic-panels {
                max-height: 58vh;
            }
            #navigation {
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="loading" id="loading">
        <div class="loading-spinner"></div>
        <span class="ml-3 text-gray-700">Loading...</span>
    </div>

    <div class="container">
        <h1 id="viewer-title" class="text-lg sm:text-2xl font-semibold text-gray-800 mb-3 sm:mb-4 text-center">Historical Dilbert Comic Viewer</h1>

        <form id="search-form" class="flex justify-center">
            <input type="date" id="search-input" placeholder="Search by date" class="rounded-md" aria-label="Search comic by date">
            <button id="search-button" type="submit" class="bg-blue-500 text-white font-medium py-2 px-4 rounded-md hover:bg-blue-700">Search</button>
        </form>

        <div id="comic-container" class="max-w-full flex justify-center mb-4 flex-col items-center">
            <img id="comic-image" src="" alt="Comic" class="rounded-lg shadow-md" style="display: none;">
            <div id="comic-panels" aria-label="Comic panels">
                <div id="comic-panels-track"></div>
            </div>
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
        const comicContainer = document.getElementById('comic-container');
        const comicPanels = document.getElementById('comic-panels');
        const comicPanelsTrack = document.getElementById('comic-panels-track');


        let currentComicIndex = 0;
        let comicFiles = [];
        let comicRoot = '/comics/';
        let currentComicPath = '';
        const panelCache = new Map();
        const PANEL_SEPARATOR_SENSITIVITY = 'normal';
        const PANEL_SENSITIVITY_PRESETS = {
            strict: {
                whiteThreshold: 248,
                minWhiteRatio: 0.993,
                rowSampleDivisor: 300,
                edgeMarginRatio: 0.04,
                minSeparatorWidthRatio: 0.003,
                minPanelWidthRatio: 0.10,
                maxPanelCount: 6
            },
            normal: {
                whiteThreshold: 245,
                minWhiteRatio: 0.985,
                rowSampleDivisor: 250,
                edgeMarginRatio: 0.03,
                minSeparatorWidthRatio: 0.002,
                minPanelWidthRatio: 0.08,
                maxPanelCount: 7
            },
            loose: {
                whiteThreshold: 238,
                minWhiteRatio: 0.965,
                rowSampleDivisor: 220,
                edgeMarginRatio: 0.02,
                minSeparatorWidthRatio: 0.001,
                minPanelWidthRatio: 0.07,
                maxPanelCount: 8
            }
        };

        function getSensitivityPreset(profileName) {
            return PANEL_SENSITIVITY_PRESETS[profileName] || PANEL_SENSITIVITY_PRESETS.normal;
        }

        function getSensitivityScanOrder() {
            const validProfiles = ['strict', 'normal', 'loose'];
            const preferredProfile = validProfiles.includes(PANEL_SEPARATOR_SENSITIVITY)
                ? PANEL_SEPARATOR_SENSITIVITY
                : 'normal';
            return [
                preferredProfile,
                ...validProfiles.filter((profileName) => profileName !== preferredProfile)
            ];
        }

        function isMobileViewport() {
            return window.matchMedia('(max-width: 768px)').matches;
        }

        function hidePanelViewer() {
            comicPanels.style.display = 'none';
            comicPanelsTrack.innerHTML = '';
            comicPanels.scrollLeft = 0;
        }

        function resetComicViewportPosition() {
            comicPanels.scrollLeft = 0;
            comicPanels.scrollTop = 0;
            comicContainer.scrollTop = 0;
        }

        function showSingleImageViewer() {
            hidePanelViewer();
            comicImage.style.display = 'block';
        }

        function showPanelViewer(panelSources) {
            comicPanelsTrack.innerHTML = '';
            panelSources.forEach((panelSource, panelIndex) => {
                const panelWrapper = document.createElement('div');
                panelWrapper.className = 'comic-panel';

                const panelImage = document.createElement('img');
                panelImage.src = panelSource;
                panelImage.alt = `Comic panel ${panelIndex + 1}`;

                panelWrapper.appendChild(panelImage);
                comicPanelsTrack.appendChild(panelWrapper);
            });

            comicImage.style.display = 'none';
            comicPanels.style.display = 'block';
            resetComicViewportPosition();
            requestAnimationFrame(() => {
                comicPanels.scrollLeft = 0;
            });
        }

        function detectPanelRanges(imageElement, sensitivityProfile = 'normal') {
            const imageWidth = imageElement.naturalWidth;
            const imageHeight = imageElement.naturalHeight;
            const preset = getSensitivityPreset(sensitivityProfile);

            if (!imageWidth || !imageHeight || imageWidth < 200) {
                return [{ start: 0, end: Math.max(0, imageWidth - 1) }];
            }

            const analysisCanvas = document.createElement('canvas');
            analysisCanvas.width = imageWidth;
            analysisCanvas.height = imageHeight;
            const analysisContext = analysisCanvas.getContext('2d', { willReadFrequently: true });
            analysisContext.drawImage(imageElement, 0, 0);
            const { data } = analysisContext.getImageData(0, 0, imageWidth, imageHeight);

            const sampledRows = [];
            const rowStep = Math.max(1, Math.floor(imageHeight / preset.rowSampleDivisor));
            for (let y = 0; y < imageHeight; y += rowStep) {
                sampledRows.push(y);
            }

            const whiteColumns = new Array(imageWidth).fill(false);

            for (let x = 0; x < imageWidth; x++) {
                let whitePixelCount = 0;
                for (let i = 0; i < sampledRows.length; i++) {
                    const y = sampledRows[i];
                    const offset = (y * imageWidth + x) * 4;
                    const red = data[offset];
                    const green = data[offset + 1];
                    const blue = data[offset + 2];
                    if (red >= preset.whiteThreshold && green >= preset.whiteThreshold && blue >= preset.whiteThreshold) {
                        whitePixelCount++;
                    }
                }
                whiteColumns[x] = (whitePixelCount / sampledRows.length) >= preset.minWhiteRatio;
            }

            const separatorRuns = [];
            let runStart = -1;
            for (let x = 0; x < imageWidth; x++) {
                if (whiteColumns[x]) {
                    if (runStart === -1) {
                        runStart = x;
                    }
                } else if (runStart !== -1) {
                    separatorRuns.push({ start: runStart, end: x - 1 });
                    runStart = -1;
                }
            }
            if (runStart !== -1) {
                separatorRuns.push({ start: runStart, end: imageWidth - 1 });
            }

            const edgeMargin = Math.floor(imageWidth * preset.edgeMarginRatio);
            const minSeparatorWidth = Math.max(2, Math.floor(imageWidth * preset.minSeparatorWidthRatio));
            const usefulSeparators = separatorRuns.filter((run) => {
                const runWidth = run.end - run.start + 1;
                return runWidth >= minSeparatorWidth && run.start > edgeMargin && run.end < imageWidth - edgeMargin;
            });

            const ranges = [];
            let segmentStart = 0;
            for (let i = 0; i < usefulSeparators.length; i++) {
                const separator = usefulSeparators[i];
                const segmentEnd = separator.start - 1;
                if (segmentEnd >= segmentStart) {
                    ranges.push({ start: segmentStart, end: segmentEnd });
                }
                segmentStart = separator.end + 1;
            }
            if (segmentStart <= imageWidth - 1) {
                ranges.push({ start: segmentStart, end: imageWidth - 1 });
            }

            const minPanelWidth = Math.max(80, Math.floor(imageWidth * preset.minPanelWidthRatio));
            const filteredRanges = ranges.filter((range) => (range.end - range.start + 1) >= minPanelWidth);

            if (filteredRanges.length <= 1 || filteredRanges.length > preset.maxPanelCount) {
                return [{ start: 0, end: imageWidth - 1 }];
            }

            return filteredRanges;
        }

        function buildPanelSources(imageElement, ranges) {
            const imageHeight = imageElement.naturalHeight;
            const panelSources = [];

            for (let i = 0; i < ranges.length; i++) {
                const range = ranges[i];
                const panelWidth = range.end - range.start + 1;
                if (panelWidth <= 0) {
                    continue;
                }

                const panelCanvas = document.createElement('canvas');
                panelCanvas.width = panelWidth;
                panelCanvas.height = imageHeight;
                const panelContext = panelCanvas.getContext('2d');
                panelContext.drawImage(
                    imageElement,
                    range.start,
                    0,
                    panelWidth,
                    imageHeight,
                    0,
                    0,
                    panelWidth,
                    imageHeight
                );
                panelSources.push(panelCanvas.toDataURL('image/png'));
            }

            return panelSources;
        }

        function getPanelDataForCurrentComic() {
            if (!currentComicPath) {
                return { hasPanels: false, panels: [] };
            }

            if (panelCache.has(currentComicPath)) {
                return panelCache.get(currentComicPath);
            }

            const sensitivityOrder = getSensitivityScanOrder();
            let selectedRanges = [{ start: 0, end: comicImage.naturalWidth - 1 }];

            for (let i = 0; i < sensitivityOrder.length; i++) {
                const sensitivityProfile = sensitivityOrder[i];
                const candidateRanges = detectPanelRanges(comicImage, sensitivityProfile);
                if (candidateRanges.length > 1) {
                    selectedRanges = candidateRanges;
                    break;
                }
            }

            if (selectedRanges.length <= 1) {
                const noPanels = { hasPanels: false, panels: [] };
                panelCache.set(currentComicPath, noPanels);
                return noPanels;
            }

            const panelSources = buildPanelSources(comicImage, selectedRanges);
            const panelData = {
                hasPanels: panelSources.length > 1,
                panels: panelSources
            };
            panelCache.set(currentComicPath, panelData);
            return panelData;
        }

        function renderComicForViewport() {
            if (!comicImage.src) {
                return;
            }

            if (!isMobileViewport()) {
                showSingleImageViewer();
                return;
            }

            const panelData = getPanelDataForCurrentComic();
            if (panelData.hasPanels) {
                showPanelViewer(panelData.panels);
            } else {
                showSingleImageViewer();
            }
        }

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

            resetComicViewportPosition();
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
            currentComicPath = comicPath;

            try {
                const imgResponse = await fetch(comicPath, { method: 'HEAD' });
                if (!imgResponse.ok) {
                    throw new Error(`Comic image not found: ${comicPath}`);
                }
                comicImage.src = comicPath;
                comicImage.onload = () => {
                    loadingIndicator.style.display = 'none';
                    comicImage.style.display = 'block';
                    hidePanelViewer();
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

                    renderComicForViewport();
                };
                comicImage.onerror = () => {
                    loadingIndicator.style.display = 'none';
                    hidePanelViewer();
                    errorMessage(`Failed to load comic: ${comicPath}`);
                };

            } catch (error) {
                console.error("Error loading comic:", error);
                loadingIndicator.style.display = 'none';
                hidePanelViewer();
                errorMessage(`Failed to load comic.  Check console for error details.`);
            }
        }

        function errorMessage(message) {
            errorMessageDisplay.textContent = message;
            errorMessageDisplay.style.display = 'block';
            navigation.style.display = 'none';
        }

        window.addEventListener('resize', () => {
            if (comicImage.complete && comicImage.naturalWidth > 0) {
                renderComicForViewport();
            }
        });

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

