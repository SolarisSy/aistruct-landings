var mask_telefone = document.getElementsByClassName('mask_telefone');
for (let i_maskTel = 0; i_maskTel < mask_telefone.length; i_maskTel++) {
    let mask = IMask(mask_telefone[i_maskTel], { mask: '(00) 00000-0000' });
}

var mask_cpf = document.getElementsByClassName('mask_cpf');
for (let i_maskCPF = 0; i_maskCPF < mask_cpf.length; i_maskCPF++) {
    let mask = IMask(mask_cpf[i_maskCPF], { mask: '000.000.000-00' });
}

var mask_cnpj = document.getElementsByClassName('mask_cnpj');
for (let i_maskCNPJ = 0; i_maskCNPJ < mask_cnpj.length; i_maskCNPJ++) {
    let mask = IMask(mask_cnpj[i_maskCNPJ], { mask: '00.000.000/0000-00' });
}

var mask_cep = document.getElementsByClassName('mask_cep');
for (let i_maskCEP = 0; i_maskCEP < mask_cep.length; i_maskCEP++) {
    let mask = IMask(mask_cep[i_maskCEP], { mask: '00000-000' });
}

var mask_real = document.getElementsByClassName('mask_real');
for (let i_maskReal = 0; i_maskReal < mask_real.length; i_maskReal++) {
    let mask = IMask(mask_real[i_maskReal], {
        mask: Number,
        scale: 2,
        signed: false,
        thousandsSeparator: '.',
        padFractionalZeros: true,
        normalizeZeros: true,
        radix: ',',
        mapToRadix: ['.'],
        prefix: 'R$ ',
        lazy: false
    });
}

var mask_data = document.getElementsByClassName('mask_data');
for (let i_maskData = 0; i_maskData < mask_data.length; i_maskData++) {
    let mask = IMask(mask_data[i_maskData], {
        mask: Date,
        pattern: 'd{/}`m{/}`Y',
        lazy: false,
        blocks: {
            d: {
                mask: IMask.MaskedRange,
                from: 1,
                to: 31,
                maxLength: 2
            },
            m: {
                mask: IMask.MaskedRange,
                from: 1,
                to: 12,
                maxLength: 2
            },
            Y: {
                mask: IMask.MaskedRange,
                from: 1900,
                to: 2099
            }
        },
        format: function (date) {
            let day = String(date.getDate()).padStart(2, '0');
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let year = date.getFullYear();
            return `${day}/${month}/${year}`;
        },
        parse: function (str) {
            let [day, month, year] = str.split('/');
            return new Date(year, month - 1, day);
        }
    });
}
