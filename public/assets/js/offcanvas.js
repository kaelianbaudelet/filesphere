try {
    document.addEventListener("DOMContentLoaded", function () {
        const toggleNavigationButtons =
            document.querySelectorAll(".toggle-navigation");
        const bodyNavigation = document.querySelector(".app-body-navigation");

        toggleNavigationButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                bodyNavigation.classList.toggle("open");
            });
        });
    });
} catch (error) {}
