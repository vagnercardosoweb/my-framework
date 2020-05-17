export function calcCsssRem(value) {
  return `${parseFloat(value / 16)}rem`;
}

export function formatMoney(money) {
  const formatter = Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

  return formatter.format(money);
}

export function normalizeMoney(money) {
  return Number(String(money).replace(/[^0-9-]/g, '')) / 100;
}

export function parsedHtmlDataset(dataset) {
  if (Object.prototype.toString.call(dataset) !== '[object DOMStringMap]') {
    return {};
  }

  return Object.entries(dataset).reduce((obj, [key, value]) => {
    try {
      value = JSON.parse(String(value));
    } catch (e) {}

    obj[key] = value;

    return obj;
  }, {});
}

/**
 * @param {String} path
 * @param {RequestInit} init
 *
 * @returns {Promise<Response>}
 */
export function fetchAsync(path, init) {
  return new Promise((resolve, reject) => {
    fetch(path, init)
      .then(async response => {
        if (response.ok === false || response.status >= 400) {
          const body = await response.json();

          reject({
            ...(body?.error ?? {}),
            message: body?.error?.message ?? response.statusText,
          });
        } else {
          resolve(await response.json());
        }
      })
      .catch(reject);
  });
}
