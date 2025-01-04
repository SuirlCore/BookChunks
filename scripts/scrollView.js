document.addEventListener("DOMContentLoaded", () => {
    const feedSelect = document.getElementById("feedSelect");
    const contentViewer = document.getElementById("contentViewer");

    let chunks = [];
    let currentFeedID = null;
    let lastSeenChunkIndex = 0;

    // Fetch available feeds and last seen chunk
    async function fetchFeedsAndContent() {
        const response = await fetch("scrollViewScript.php?action=fetchFeeds");
        const data = await response.json();
        populateFeeds(data.feeds);

        if (data.feeds.length > 0) {
            currentFeedID = data.feeds[0].feedID;
            fetchChunks(currentFeedID, data.lastSeenChunkID);
        }
    }

    // Populate feeds dropdown
    function populateFeeds(feeds) {
        feedSelect.innerHTML = feeds.map(feed => `<option value="${feed.feedID}">${feed.feedName}</option>`).join("");
    }

    // Fetch chunks for a feed
    async function fetchChunks(feedID, lastSeenChunkID) {
        const response = await fetch(`scrollViewScript.php?action=fetchChunks&feedID=${feedID}`);
        const data = await response.json();
        chunks = data.chunks;
        displayChunks(lastSeenChunkID);
    }

    // Display chunks and scroll to the last seen chunk
    function displayChunks(lastSeenChunkID) {
        contentViewer.innerHTML = chunks.map(chunk => `<div data-id="${chunk.chunkID}">${chunk.chunkContent}</div>`).join("");

        if (lastSeenChunkID) {
            const lastSeenElement = contentViewer.querySelector(`[data-id="${lastSeenChunkID}"]`);
            if (lastSeenElement) {
                lastSeenElement.scrollIntoView();
            }
        }
    }

    // Update last seen chunk on scroll
    contentViewer.addEventListener("scroll", () => {
        const visibleChunk = Array.from(contentViewer.children).find(chunk => {
            const rect = chunk.getBoundingClientRect();
            return rect.top >= 0 && rect.bottom <= window.innerHeight;
        });

        if (visibleChunk) {
            const chunkID = visibleChunk.getAttribute("data-id");
            updateLastSeenChunk(currentFeedID, chunkID);
        }
    });

    // Update the last seen chunk in the database
    async function updateLastSeenChunk(feedID, chunkID) {
        await fetch("scrollViewScript.php?action=updateLastSeenChunk", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ feedID, chunkID }),
        });
    }

    // Fetch new chunks when feed changes
    feedSelect.addEventListener("change", () => {
        currentFeedID = feedSelect.value;
        fetchChunks(currentFeedID);
    });

    fetchFeedsAndContent();
});
