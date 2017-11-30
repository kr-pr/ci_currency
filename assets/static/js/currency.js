(function currencyModule() {

    const currencySelectors = {
        toggle: 'a.toggle-currency-selector',
        select: 'select.currency',
        price: 'p.price'
    }

    const setCurrencyToggler = (targets) => {
    
        const makeToggler = (id) => {
            const toggler = (event) => {
                select = targets.select.filter((el)=>el.getAttribute("data-id")==id)[0]
                select.style.display = (select.style.display=="none")?"inline":"none"
            }
            return toggler
        }

        targets.toggle.forEach((el) => {
            let toggler = makeToggler(el.getAttribute("data-id"))
            el.addEventListener('click', toggler)
        });
    }

    const setCurrencyChanger = (initCurrency, currencyData, targets) => {
    
        const setSessionCurrencyURL = '/currency'

        const currencyChanger = (event) => {
            const newCurrency = event.target.value
            const newRate = currencyData[newCurrency].rate

            targets.price.forEach((el) => {
                let basePrice = parseFloat(el.getAttribute("data-price"))
                let newPrice = Math.round(basePrice*newRate)/100
                el.textContent = newPrice.toFixed(2)+currencyData[newCurrency].symbol
            });

            targets.select.forEach((el) => el.value = newCurrency);
            targets.toggle.forEach((el) => {
                el.textContent = el.textContent.replace(/ \(\S+\)/,` (${currencyData[newCurrency].symbol})`)
            });
            const jsonHeaders = new Headers({
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/json'});

            fetch(setSessionCurrencyURL, {
                method: 'POST',
                credentials: 'include',
                headers: jsonHeaders,
                body: JSON.stringify({currency: newCurrency})
            }).then((resp)=> resp.text())
            .then((text) => console.log(text))
            .catch((err) => console.log(err));

            event.target.style.display = "none";

        }

        targets.select.forEach((el) => el.addEventListener('change', currencyChanger));
    }

    const initCurrencyModule = () => {

        const targets = Object.assign({}, ...Object.keys(currencySelectors)
        .map(key => ({
            [key]: Array.from(document.querySelectorAll(currencySelectors[key]))
        })));
        
        el = targets.select[0];
        const currencies = Array.from(el.options).map((option)=>option.value);
        const current =  el.options[el.selectedIndex].value;
        const currencyData = Object.assign({}, ...currencies
            .map((code, index)=>({ [code]: {
                symbol: el.options[index].getAttribute('data-symbol'),
                rate: parseFloat(el.options[index].getAttribute('data-rate'))
            }})))

        setCurrencyChanger(current, currencyData, targets)
        setCurrencyToggler(targets)
    }

    document.addEventListener('DOMContentLoaded', initCurrencyModule, false);
})();