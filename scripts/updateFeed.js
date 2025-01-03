document.addEventListener("DOMContentLoaded", () => {
    const feedSelect = document.getElementById("feedSelect");
    const bookList = document.getElementById("bookList");
    const addToFeedBtn = document.getElementById("addToFeedBtn");
    const newFeedBtn = document.getElementById("newFeedBtn");
    const newFeedForm = document.getElementById("newFeedForm");
    const newFeedName = document.getElementById("newFeedName");
    const newFeedDescription = document.getElementById("newFeedDescription");
    const createFeedBtn = document.getElementById("createFeedBtn");

    // Fetch feeds and books
    fetch("updateFeedGet.php?data=feeds")
        .then(response => response.json())
        .then(data => {
            if (data.feeds.length === 0) {
                newFeedForm.style.display = "block";
                feedSelect.style.display = "none";
                addToFeedBtn.style.display = "none";
            } else {
                data.feeds.forEach(feed => {
                    const option = document.createElement("option");
                    option.value = feed.feedID;
                    option.textContent = feed.feedName;
                    feedSelect.appendChild(option);
                });
            }
        });

    fetch("updateFeddGet.php?data=books")
        .then(response => response.json())
        .then(data => {
            data.books.forEach(book => {
                const div = document.createElement("div");
                div.classList.add("book-item");
                div.innerHTML = `
                    <input type="checkbox" id="book-${book.bookID}" value="${book.bookID}">
                    <label for="book-${book.bookID}">${book.filename}</label>
                `;
                bookList.appendChild(div);
            });
        });

    // Show new feed form
    newFeedBtn.addEventListener("click", () => {
        newFeedForm.style.display = "block";
    });

    // Create new feed
    createFeedBtn.addEventListener("click", () => {
        const name = newFeedName.value.trim();
        const description = newFeedDescription.value.trim();

        if (name === "") {
            alert("Feed name is required.");
            return;
        }

        fetch("updateFeedCreate.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ feedName: name, feedDescription: description })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                window.location.reload(); // Reload the page to refresh feeds
            }
        })
        .catch(err => console.error(err));
    });

    // Add books to feed
    addToFeedBtn.addEventListener("click", () => {
        const selectedFeed = feedSelect.value;
        const selectedBooks = Array.from(
            document.querySelectorAll("#bookList input:checked")
        ).map(input => input.value);

        fetch("updateFeedUpdate.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ feedID: selectedFeed, bookIDs: selectedBooks })
        })
        .then(response => response.json())
        .then(data => alert(data.message))
        .catch(err => console.error(err));
    });
});
