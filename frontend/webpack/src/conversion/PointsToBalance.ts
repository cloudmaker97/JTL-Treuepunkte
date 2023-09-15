export default class PointsToBalance {
    private readonly _widgetElement: Element;
    private readonly _inputElement: HTMLInputElement;
    private readonly _inputSubmit: HTMLInputElement;

    constructor(widgetElement: Element) {
        this._widgetElement = widgetElement;
        this._inputElement = this.widgetElement.querySelector("[data-ref='bonusPointsExchangeInput']");
        this._inputSubmit = this.widgetElement.querySelector("[data-ref='bonusPointsExchangeInputSubmit']");

        // Check validity after clicking the submit button
        this.inputSubmit.addEventListener('click', () => {
            this.inputElement.reportValidity();
            // @todo logic for ajax conversion
        });

        // Add the event listeners for input element events like change and input
        this.inputElement.addEventListener('input', this.updatePreviewConversionAfterInput.bind(this));
        this.inputElement.addEventListener('change', this.updatePreviewConversionAfterInput.bind(this));

    }

    /**
     * Validates the input field in the frontend for the user and
     * update the preview of the exchange rate from points into real money (euro)
     * @private
     */
    private updatePreviewConversionAfterInput() {
        this.inputElement.reportValidity()
        let previewConversion: any = this.calculatePointsToEuro(this.currentInputNumber).toFixed(2);
        if(isNaN(previewConversion)) return;
        previewConversion = previewConversion.toString().replace(".", ",");
        this.previewConversion.textContent = previewConversion;
    }

    /**
     * Calculate an amount of points into real currency money (euro)
     * @param points
     */
    calculatePointsToEuro(points: number): number {
        return points / this.conversionRateEuroInPoints;
    }

    /**
     * Get the current input field value as parsed number
     */
    get currentInputNumber(): number {
        return parseInt(
            this.inputElement.value
        );
    }

    /**
     * Get the dom element, containing the exchange rate preview
     */
    get previewConversion(): Element {
        return this.widgetElement.querySelector("[data-ref='bonusPointsExchangeRate']")
    }

    /**
     * Get the amount of points that represent one real currency euro in exchange
     */
    get conversionRateEuroInPoints(): number {
        return parseInt(
            this.widgetElement.querySelector("[data-exchange-rate]").getAttribute("data-exchange-rate")
        );
    }

    /**
     * Get the widget dom element
     */
    get widgetElement(): Element {
        return this._widgetElement;
    }

    /**
     * Get the input field
     */
    get inputElement(): HTMLInputElement {
        return this._inputElement;
    }

    /**
     * Get the input submit button
     */
    get inputSubmit(): HTMLInputElement {
        return this._inputSubmit;
    }
}