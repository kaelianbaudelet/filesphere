const dropArea = document.getElementById("drop-area");
const fileInput = document.getElementById("file");
const fileInfo = document.getElementById("file-info");

dropArea.addEventListener("dragover", (event) => {
    event.preventDefault();
    dropArea.classList.add("dragging");
});

dropArea.addEventListener("dragleave", () => {
    dropArea.classList.remove("dragging");
});

dropArea.addEventListener("drop", (event) => {
    event.preventDefault();
    dropArea.classList.remove("dragging");
    const files = event.dataTransfer.files;
    if (files.length > 10) {
        alert("Vous ne pouvez téléverser que 10 fichiers au maximum.");
    } else {
        fileInput.files = files;
        updateFileInfo(files);
    }
});

fileInput.addEventListener("change", function () {
    if (this.files.length > 10) {
        alert("Vous ne pouvez téléverser que 10 fichiers au maximum.");
        this.value = "";
        fileInfo.textContent = "";
    } else {
        updateFileInfo(this.files);
    }
});

function updateFileInfo(files) {
    const fileNames = Array.from(files)
        .map((file) => file.name)
        .join(", ");
    fileInfo.textContent = `${files.length} fichier(s) sélectionné(s): ${fileNames}`;
}
