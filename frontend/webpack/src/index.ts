import "../stylesheets/main.css"
import PointsToBalance from "./conversion/PointsToBalance";


document.addEventListener("DOMContentLoaded", () => {
    let widget: Element = document.querySelector("[data-ref='bonusPointsExchangeWidget']")
    new PointsToBalance(widget);
});
