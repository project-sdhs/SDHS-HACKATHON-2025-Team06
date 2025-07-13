const cate = document.querySelector("#category");
const budget = document.querySelector("#budget");
const selectButton = document.querySelector("#recommendation");

selectButton.addEventListener("click", function () {


    const selectedCategory = cate.value;
    const selectedBudget = budget.value;
    location.href = `../showStore/store.php?category=${selectedCategory}&budget=${selectedBudget}`;


})