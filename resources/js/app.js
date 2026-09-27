import "./bootstrap";

const activeFilterClasses = ["bg-slate-900", "text-white", "shadow-sm"];
const inactiveFilterClasses = ["text-slate-600", "hover:bg-white"];

document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-candidate-filter]");
    const group = button?.closest("[data-candidate-filter-group]");

    if (!button || !group) {
        return;
    }

    const selectedGender = button.dataset.candidateFilter;

    group
        .querySelectorAll("[data-candidate-filter]")
        .forEach((filterButton) => {
            const isActive = filterButton === button;
            const classesToAdd = isActive
                ? activeFilterClasses
                : inactiveFilterClasses;
            const classesToRemove = isActive
                ? inactiveFilterClasses
                : activeFilterClasses;

            filterButton.setAttribute("aria-pressed", String(isActive));
            filterButton.classList.remove(...classesToRemove);
            filterButton.classList.add(...classesToAdd);
        });

    group.querySelectorAll("[data-candidate-gender]").forEach((candidate) => {
        candidate.hidden =
            selectedGender !== "all" &&
            candidate.dataset.candidateGender !== selectedGender;
    });
});
