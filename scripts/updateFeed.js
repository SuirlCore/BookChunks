document.addEventListener("DOMContentLoaded", () => {
    const feedSelect = document.getElementById("feedSelect");
    const availableBooks = document.getElementById("availableBooks");
    const feedBooks = document.getElementById("feedBooks");
    const updateFeedBtn = document.getElementById("updateFeed");

    // Fetch feeds, books, and populate the UI
    async function fetchInitialData() {
        const response = await fetch("updateFeedScript.php?action=init");
        const data = await response.json();
        populateFeeds(data.feeds);
        populateBooks(data.books);
    }

    // Populate feed dropdown
    function populateFeeds(feeds) {
        feedSelect.innerHTML = feeds.map(feed => `<option value="${feed.feedID}">${feed.feedName}</option>`).join("");
    }

    // Populate available books
    function populateBooks(books) {
        availableBooks.innerHTML = books.map(book => `<li data-id="${book.textID}">${book.filename}</li>`).join("");
    }

    // Drag and drop functionality for books
    availableBooks.addEventListener("click", e => {
        if (e.target.tagName === "LI") {
            feedBooks.appendChild(e.target);
        }
    });

    feedBooks.addEventListener("click", e => {
        if (e.target.tagName === "LI") {
            availableBooks.appendChild(e.target);
        }
    });

    // Update feed on button click
    updateFeedBtn.addEventListener("click", async () => {
        const feedID = feedSelect.value;
        const bookOrder = Array.from(feedBooks.children).map((li, index) => ({
            bookID: li.getAttribute("data-id"),
            position: index + 1,
        }));

        const response = await fetch("updateFeedScript.php?action=updateFeed", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ feedID, bookOrder }),
        });

        const result = await response.json();
        alert(result.message);
    });

    fetchInitialData();
});
