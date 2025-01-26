<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dynamic Tooltip for All Words</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
    }
    .tooltip {
      position: absolute;
      background: #333;
      color: #fff;
      padding: 10px;
      border-radius: 8px;
      font-size: 0.9em;
      box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
      display: none;
      max-width: 300px;
      z-index: 1000;
    }
    .word {
      color: blue;
      text-decoration: underline;
      cursor: pointer;
    }
    .loading {
      font-style: italic;
      color: #aaa;
    }
  </style>
</head>
<body>
  <div id="text-container">
    This is a sample paragraph. Click any word to see its definition. It dynamically fetches definitions from an online dictionary.
  </div>

  <div id="tooltip" class="tooltip"></div>

  <script>
    const tooltip = document.getElementById('tooltip');
    const textContainer = document.getElementById('text-container');

    // Function to wrap each word in the div with a span
    function wrapWordsWithSpans(container) {
      const text = container.textContent;
      const words = text.split(/\s+/); // Split by spaces
      container.innerHTML = words
        .map(word => `<span class="word" data-word="${word}">${word}</span>`)
        .join(' ');
    }

    // Function to fetch the definition of a word
    async function fetchDefinition(word) {
      const apiUrl = `https://api.dictionaryapi.dev/api/v2/entries/en/${word}`;
      try {
        const response = await fetch(apiUrl);
        if (!response.ok) throw new Error("Definition not found.");
        const data = await response.json();

        // Get the first definition
        const meanings = data[0].meanings;
        if (meanings && meanings.length > 0) {
          const definition = meanings[0].definitions[0].definition;
          return definition;
        }
        throw new Error("Definition not found.");
      } catch (error) {
        return error.message;
      }
    }

    // Event listener for tooltip functionality
    function enableTooltip() {
      const words = document.querySelectorAll('.word');
      words.forEach(word => {
        word.addEventListener('click', async (e) => {
          const wordText = word.getAttribute('data-word');
          const rect = word.getBoundingClientRect();

          tooltip.textContent = "Loading...";
          tooltip.classList.add('loading');
          tooltip.style.display = 'block';
          tooltip.style.left = `${rect.left + window.scrollX}px`;
          tooltip.style.top = `${rect.bottom + window.scrollY + 5}px`;

          // Fetch the definition dynamically
          const definition = await fetchDefinition(wordText);
          tooltip.textContent = definition;
          tooltip.classList.remove('loading');
        });
      });
    }

    // Initialize
    wrapWordsWithSpans(textContainer);
    enableTooltip();

    // Hide the tooltip if you click anywhere else
    document.addEventListener('click', (e) => {
      if (!e.target.classList.contains('word')) {
        tooltip.style.display = 'none';
      }
    });
  </script>
</body>
</html>
