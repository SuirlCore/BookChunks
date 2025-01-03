document.addEventListener("DOMContentLoaded", () => {
    const feedSelect = document.getElementById("feedSelect");
    const bookList = document.getElementById("bookList");
    const feedList = document.getElementById("feedList");
    const newFeedBtn = document.getElementById("newFeedBtn");
    const newFeedForm = document.getElementById("newFeedForm");
    const newFeedName = document.getElementById("newFeedName");
    const newFeedDescription = document.getElementById("newFeedDescription");
    const createFeedBtn = document.getElementById("createFeedBtn");
    const updateFeedBtn = document.getElementById("updateFeedBtn");

    function fetchFeedsAndBooks() {
        // Fetch feeds
        fetch("updateFeedFetch.php?data=feeds")
            .then(response => response.json())
            .then(data => {
                feedSelect.innerHTML = ""; // Clear existing options
                if (data.feeds.length === 0) {
                    newFeedForm.style.display = "block";
                } else {
                    newFeedForm.style.display = "none";
                    data.feeds.forEach(feed => {
                        const option = document.createElement("option");
                        option.value = feed.feedID;
                        option.textContent = feed.feedName;
                        feedSelect.appendChild(option);
                    });
                    loadFeed(feedSelect.value);
                }
            });

        // Fetch available books
        fetch("updateFeedFetch.php?data=books")
            .then(response => response.json())
            .then(data => {
                bookList.innerHTML = ""; // Clear existing books
                data.books.forEach(book => {
                    const div = document.createElement("div");
                    div.classList.add("book-item");
                    div.innerHTML = `
                        <button data-book-id="${book.bookID}" class="addBookBtn">Add</button>
                        ${book.filename}
                    `;
                    bookList.appendChild(div);
                });

                document.querySelectorAll(".addBookBtn").forEach(button => {
                    button.addEventListener("click", () => {
                        const bookID = button.getAttribute("data-book-id");
                        const bookName = button.nextSibling.textContent.trim();
                        addBookToFeed(bookID, bookName);
                    });
                });
            });
    }

    function loadFeed(feedID) {
        fetch(`updateFeedFetch.php?feedID=${feedID}`)
            .then(response => response.json())
            .then(data => {
                feedList.innerHTML = ""; // Clear existing feed items
                if (data.feedBooks) {
                    data.feedBooks.forEach(book => {
                        const li = document.createElement("li");
                        li.dataset.bookId = book.bookID;
                        li.textContent = book.filename;
                        li.innerHTML += `
                            <button class="removeBookBtn">Remove</button>
                        `;
                        feedList.appendChild(li);
                    });

                    document.querySelectorAll(".removeBookBtn").forEach(button => {
                        button.addEventListener("click", () => {
                            const li = button.parentElement;
                            feedList.removeChild(li);
                        });
                    });

                    makeFeedSortable();
                }
            })
            .catch(err => console.error("Error loading feed:", err));
    }

    function addBookToFeed(bookID, bookName) {
        const li = document.createElement("li");
        li.dataset.bookId = bookID;
        li.textContent = bookName;
        li.innerHTML += `
            <button class="removeBookBtn">Remove</button>
        `;
        feedList.appendChild(li);

        li.querySelector(".removeBookBtn").addEventListener("click", () => {
            feedList.removeChild(li);
        });
    }

    function makeFeedSortable() {
        const sortable = new Sortable(feedList, {
            animation: 150,
        });
    }

    updateFeedBtn.addEventListener("click", () => {
        const feedID = feedSelect.value;
        const books = Array.from(feedList.children).map((li, index) => ({
            bookID: li.dataset.bookId,
            position: index,
        }));

        fetch("updateFeedUpdate.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ feedID, books }),
        })
            .then(response => response.json())
            .then(data => alert(data.message))
            .catch(err => console.error("Error updating feed:", err));
    });

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
            body: JSON.stringify({ feedName: name, feedDescription: description }),
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    fetchFeedsAndBooks();
                }
            });
    });

    newFeedBtn.addEventListener("click", () => {
        newFeedForm.style.display = "block";
    });

    feedSelect.addEventListener("change", () => {
        loadFeed(feedSelect.value);
    });

    fetchFeedsAndBooks();
});
