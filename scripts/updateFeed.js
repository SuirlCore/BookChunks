document.addEventListener("DOMContentLoaded", () => {
    const feedSelect = document.getElementById("feedSelect");
    const bookList = document.getElementById("bookList");
    const addToFeedBtn = document.getElementById("addToFeedBtn");

    // Fetch feeds and books
    fetch("updateFeedGet.php?data=feeds")
        .then(response => response.json())
        .then(data => {
            data.feeds.forEach(feed => {
                const option = document.createElement("option");
                option.value = feed.feedID;
                option.textContent = feed.feedName;
                feedSelect.appendChild(option);
            });
        });

    fetch("updateFeedGet.php?data=books")
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
