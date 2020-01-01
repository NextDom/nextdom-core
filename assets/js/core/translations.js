/**
 * return jsonObject from NextdomCore with all translations
 * @returns {Promise<any>}
 */
const getTranslations = async () => {
    const response = await fetch('/core/ajax/translations.ajax.php');
    return await response.json();
};

//persist and return values of translations from localstorage
function persistInLocalStorage(key,json) {
    let translations = null;
    if (localStorage.getItem(key) !== null ){
        localStorage.setItem(key,JSON.stringify(json));
        translations = json;
    } else {
        translations = JSON.parse(localStorage.getItem(key));
    }
    return  translations ;
}

await getTranslations();
