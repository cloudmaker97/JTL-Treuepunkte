// Load stylesheet and tailwind/css
import "../stylesheets/main.css"
import PointsToBalance from "./conversion/PointsToBalance";

document.addEventListener("DOMContentLoaded", () => {
    /**
     * Load an exchange widget for converting loyalty points into real money. The settings
     * for this behaviour are defined in the administrative section (backend) of the plugin.
     */
    function loadExchangeWidget() {
        let widget: Element = document.querySelector("[data-ref='bonusPointsExchangeWidget']")
        if (widget) {
            new PointsToBalance(widget);
        } else {
            console.warn("The script was loaded on a page without an exchange widget.");
        }
    }
    loadExchangeWidget();
});
