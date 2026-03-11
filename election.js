function nextCategory(currentCategory) {
    const currentCategoryDiv = document.getElementById(`category${currentCategory}`);
    const nextCategoryDiv = document.getElementById(`category${currentCategory + 1}`);

    if (nextCategoryDiv) {
        currentCategoryDiv.style.display = 'none';
        nextCategoryDiv.style.display = 'block';
    }
}