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
            this.inputValidityCheck();
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
        this.inputValidityCheck();
        let previewConversion: any = this.calculatePointsToEuro(this.currentInputNumber).toFixed(2);
        if(isNaN(previewConversion)) return;
        if(previewConversion < 0) return;
        previewConversion = previewConversion.toString().replace(".", ",");
        this.previewConversion.textContent = previewConversion;
        this.previewConversionInput.textContent = this.inputElement.value.toString();
    }

    /**
     * Checks the exchange preview input for validity
     * @private
     */
    private inputValidityCheck(): boolean {
        let validateResult: boolean = this.inputElement.reportValidity();

        // User haptic, red border on validity issues
        const errorClass = "!border-[#FF0000]";
        if(validateResult) {
            this.inputElement.classList.remove(errorClass);
        } else {
            this.inputElement.classList.add(errorClass);
        }
        return validateResult;
    }

    /**
     * Calculate an amount of points into real currency money (euro)
     * @param points
     */
    private calculatePointsToEuro(points: number): number {
        return points / this.conversionRateEuroInPoints;
    }

    /**
     * Get the current input field value as parsed number
     */
    private get currentInputNumber(): number {
        return parseInt(
            this.inputElement.value
        );
    }

    /**
     * Get the dom element, containing the exchange rate preview
     */
    private get previewConversion(): Element {
        return this.widgetElement.querySelector("[data-ref='bonusPointsExchangeRate']")
    }

    /**
     * Get the dom element, containing the input of the exchange preview
     */
    private get previewConversionInput(): Element {
        return this.widgetElement.querySelector("[data-ref='bonusPointsInput']");
    }

    /**
     * Get the amount of points that represent one real currency euro in exchange
     */
    private get conversionRateEuroInPoints(): number {
        return parseInt(
            this.widgetElement.querySelector("[data-exchange-rate]").getAttribute("data-exchange-rate")
        );
    }

    /**
     * Get the widget dom element
     */
    private get widgetElement(): Element {
        return this._widgetElement;
    }

    /**
     * Get the input field
     */
    private get inputElement(): HTMLInputElement {
        return this._inputElement;
    }

    /**
     * Get the input submit button
     */
    private get inputSubmit(): HTMLInputElement {
        return this._inputSubmit;
    }
}