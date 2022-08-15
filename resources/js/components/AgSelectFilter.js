export default class AgSelectFilter {
   init(params) {
        console.log('init', params);

        this.currentValue = null;
        this.column = params.column;

        let optionsHtml = '';

        params.options.map(o => {
            optionsHtml += `<option value="${o.value}">${o.name}</option>`;
        })

        this.eGui = document.createElement('div');
        this.eGui.style = 'display: flex; justify-content: center;';
        this.eGui.innerHTML = `
        <div class="ag-floating-filter-input" role="presentation">
            <!--AG-SELECT-TEXT-FIELD-->
            
            <div ref="eDateWrapper" style="display: flex;">
                <div class="ag-filter-filter">
                    <!--AG-INPUT-TEXT-FIELD-->
                    <div role="presentation" class="ag-date-filter ag-labeled ag-label-align-left ag-text-field ag-input-field" ref="eDateInput">
                        <div ref="eLabel" class="ag-input-field-label ag-label ag-hidden ag-text-field-label" role="presentation">
                            Выберите статус
                        </div>
                        <div ref="eWrapper" class="ag-wrapper ag-input-wrapper ag-text-field-input-wrapper" role="presentation">
                            <select ref="eInput" class="ag-input-field-input ag-text-field-input" id=""  aria-label="Select Filter" placeholder='Выберите значение'
                                style="border: var(--ag-borders-input) var(--ag-input-border-color); padding-left: var(--ag-grid-size); min-height: calc(var(--ag-grid-size) * 4); border-radius: 4px; padding: 2px 6px; "
                            >
                                ${optionsHtml}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `;
        this.eFilterInput = this.eGui.querySelector('select');
        this.eFilterInput.style.color = params.color;

        const onInputBoxChanged = (e) => {
            this.currentValue = this.eFilterInput.value;
            params.filterChangedCallback();
        }

        this.eFilterInput.addEventListener('change', onInputBoxChanged);
    }

    getGui() {
        console.log(this.eGui);
        return this.eGui;
    }    

    doesFilterPass(params) {
        console.log('pass?', params.data[this.column], this.currentValue);
        
        let passed = true;
        
        if (params.data[this.column] != this.currentValue) {
            passed = false;
        }
    
        return passed;
    }

    isFilterActive() {
        return this.currentValue != null && this.currentValue !== '';
    }

    getModel() {
        if (!this.isFilterActive()) {
            return null;
        }
    
        return { value: this.currentValue };
    }
    
    setModel(model) {
        this.eFilterInput.value = model == null ? null : model.value;
    }
}