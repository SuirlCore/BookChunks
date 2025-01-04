document.addEventListener("DOMContentLoaded", () => {
    const feedSelect = document.getElementById("feedSelect");
    const availableBooks = document.getElementById("availableBooks");
    const feedBooks = document.getElementById("feedBooks");
    const updateFeedBtn = document.getElementById("updateFeed");
    const createFeedForm = document.getElementById("create-feed-form");

    async function fetchInitialData() {
        const response = await fetch("updateFeedScript.php?action=init");
        const data = await response.json();
        populateFeeds(data.feeds);
        populateBooks(data.books);
    }

    function populateFeeds(feeds) {
        if (feeds.length === 0) {
            feedSelect.innerHTML = '<option disabled>No feeds available</option>';
            return;
        }
        feedSelect.innerHTML = feeds.map(feed => `<option value="${feed.feedID}">${feed.feedName}</option>`).join("");
    }

    function populateBooks(books) {
        availableBooks.innerHTML = books.map(book => `<li data-id="${book.textID}">${book.filename}</li>`).join("");
    }

    createFeedForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(createFeedForm);
        const response = await fetch("updateFeedScript.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "createFeed",
                feedName: formData.get("feedName"),
                feedDescription: formData.get("feedDescription"),
            }),
        });
        const result = await response.json();
        alert(result.message);
        fetchInitialData();
    });

    updateFeedBtn.addEventListener("click", async () => {
        const feedID = feedSelect.value;
        const bookOrder = Array.from(feedBooks.children).map((li, index) => ({
            bookID: li.dataset.id,
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
