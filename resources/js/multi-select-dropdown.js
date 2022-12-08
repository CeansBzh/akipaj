const MultiSelectDropdown = (params) => {
  let config = {
    search: true,
    hideX: false,
    useStyles: true,
    placeholder: 'Sélectionner...',
    txtSelected: 'éléments sélectionnés',
    txtRemove: 'Retirer',
    txtSearch: 'Rechercher...',
    minWidth: '160px',
    maxWidth: '100%',
    maxHeight: '180px',
    borderRadius: 6,
    ...params
  };

  const newElement = (tag, params) => {
    let element = document.createElement(tag);
    if (params) {
      Object.keys(params).forEach((key) => {
        if (key === 'class') {
          Array.isArray(params[key])
            ? params[key].forEach((o) => (o !== '' ? element.classList.add(o) : 0))
            : params[key] !== ''
              ? element.classList.add(params[key])
              : 0;
        } else if (key === 'style') {
          Object.keys(params[key]).forEach((value) => {
            element.style[value] = params[key][value];
          });
        } else if (key === 'text') {
          params[key] === '' ? (element.innerHTML = '&nbsp;') : (element.innerText = params[key]);
        } else {
          element[key] = params[key];
        }
      });
    }
    return element;
  };

  document.querySelectorAll('select[multiple]').forEach((multiSelect) => {
    let div = newElement('div', { class: 'multiselect-dropdown' });
    multiSelect.style.display = 'none';
    multiSelect.parentNode.insertBefore(div, multiSelect.nextSibling);
    let dropdownListWrapper = newElement('div', { class: 'multiselect-dropdown-list-wrapper' });
    let dropdownList = newElement('div', { class: 'multiselect-dropdown-list' });
    let search = newElement('input', {
      type: 'text',
      class: ['multiselect-dropdown-search'].concat([config.searchInput?.class ?? 'form-control']),
      style: {
        width: '100%',
        display: config.search ? 'block' : multiSelect.attributes.search?.value === 'true' ? 'block' : 'none'
      },
      placeholder: config.txtSearch
    });
    dropdownListWrapper.appendChild(search);
    div.appendChild(dropdownListWrapper);
    dropdownListWrapper.appendChild(dropdownList);

    multiSelect.loadOptions = () => {
      dropdownList.innerHTML = '';

      Array.from(multiSelect.querySelectorAll('optgroup')).map((optgroup) => {
        let optionGroup = newElement('div', { class: 'option-group', text: optgroup.label });
        dropdownList.appendChild(optionGroup);
        Array.from(optgroup.children).map((option) => {
          let optionElement = newElement('div', { class: option.selected ? 'checked option' : 'option', srcElement: option });
          let optionCheckbox = newElement('input', { type: 'checkbox', checked: option.selected });
          optionElement.appendChild(optionCheckbox);
          optionElement.appendChild(newElement('label', { text: option.text }));

          optionElement.addEventListener('click', () => {
            optionElement.classList.toggle('checked');
            optionElement.querySelector('input').checked = !optionElement.querySelector('input').checked;
            optionElement.srcElement.selected = !optionElement.srcElement.selected;
            multiSelect.dispatchEvent(new Event('change'));
          });
          optionCheckbox.addEventListener('click', () => {
            optionCheckbox.checked = !optionCheckbox.checked;
          });
          option.optionElement = optionElement;
          dropdownList.appendChild(optionElement);
        });
      });

      div.dropdownListWrapper = dropdownListWrapper;

      div.refresh = () => {
        search.value = '';
        dropdownListWrapper.classList.remove('searching');
        dropdownList.querySelectorAll(':scope div.option').forEach((div) => {
          div.style.display = 'flex';
        });
        div.querySelectorAll('span.optext, span.placeholder').forEach((placeholder) => div.removeChild(placeholder));
        let selected = Array.from(multiSelect.selectedOptions);
        if (selected.length > (multiSelect.attributes['max-items']?.value ?? 3)) {
          div.appendChild(
            newElement('span', {
              class: ['optext', 'maxselected'],
              text: selected.length + ' ' + config.txtSelected
            })
          );
        } else {
          selected.map((option) => {
            let span = newElement('span', {
              class: 'optext',
              text: option.text,
              srcElement: option
            });
            if (!config.hideX) {
              span.appendChild(
                newElement('span', {
                  class: 'optdel',
                  text: '🗙',
                  title: config.txtRemove,
                  onclick: (e) => {
                    span.srcElement.optionElement.dispatchEvent(new Event('click'));
                    div.refresh();
                    e.stopPropagation();
                  }
                })
              );
            }
            div.appendChild(span);
          });
        }
        if (multiSelect.selectedOptions?.length === 0) {
          div.appendChild(
            newElement('span', {
              class: 'placeholder',
              text: multiSelect.attributes?.placeholder?.value ?? config.placeholder
            })
          );
        }
      };
      div.refresh();
    };
    multiSelect.loadOptions();

    search.addEventListener('input', () => {
      dropdownListWrapper.classList.toggle('searching', search.value !== '');
      dropdownList.querySelectorAll(':scope div.option').forEach((div) => {
        let innerText = div.querySelector('label').innerText.toLowerCase();
        div.style.display = innerText.includes(search.value.toLowerCase()) ? 'flex' : 'none';
      });
    });

    div.addEventListener('click', () => {
      div.dropdownListWrapper.style.display = 'block';
      search.focus();
      search.select();
    });

    document.addEventListener('click', (e) => {
      if (!div.contains(e.target)) {
        dropdownListWrapper.style.display = 'none';
        div.refresh();
      }
    });
  });

  const createStyles = () => {
    let styles = {
      ':root': {
        '--color-background': '#ffffff',
        '--color-border': '#ced4da',
        '--color-background--option': '#d6dde6',
        '--color-background--option--hover': '#cbd5e0a1',
        '--color-text--normal': '#0c0c0c',
        '--color-text--grey': '#24262c',
        '--color-text--red': '#cc6666',
        '--color-text--placeholder': '#ced4da',
        '--border-radius--base': `${parseInt(config.borderRadius)}px` || '6px',
        '--border-radius--small': `${parseInt(config.borderRadius) * 0.75}px` || '4px'
      },
      '.multiselect-dropdown': {
        position: 'relative',
        display: 'inline-flex',
        'flex-wrap': 'wrap',
        padding: '0.5rem 0.75rem',
        gap: '6px',
        'border-radius': 'var(--border-radius--base)',
        border: 'solid 1px var(--color-border)',
        background: 'var(--color-background)',
        'background-image':
          "url(\"data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e\")",
        'background-repeat': 'no-repeat',
        'background-position': 'right 6px center',
        'background-size': '16px 12px',
        'min-width': `${config.minWidth}` ?? '140px',
        'width': '100%'
      },
      'span.optext, span.placeholder': {
        display: 'inline-flex',
        'justify-content': 'center',
        'align-items': 'center',
        'font-size': '16px',
        'border-radius': 'var(--border-radius--small)'
      },
      'span.optext': {
        'background-color': 'var(--color-background--option)',
        padding: '0 12px 2px 6px',
        cursor: 'default',
        '-webkit-user-select': 'none',
        '-moz-user-select': 'none',
        '-ms-user-select': 'none',
        'user-select': 'none'
      },
      'span.optext .optdel': {
        float: 'right',
        margin: '0 -6px 1px 6px',
        'font-size': '12px',
        cursor: 'pointer',
        color: 'var(--color-text--grey)'
      },
      'span.optext .optdel:hover': {
        color: 'var(--color-text--red)'
      },
      'span.placeholder': {
        color: 'rgba(0, 0, 0, 0.7)',
      },
      '.multiselect-dropdown-list-wrapper': {
        'z-index': 100,
        'border-radius': 'var(--border-radius--base)',
        border: 'solid 1px var(--color-border)',
        display: 'none',
        margin: '-1px',
        position: 'absolute',
        top: 0,
        left: 0,
        right: 0,
        background: 'var(--color-background)'
      },
      '.multiselect-dropdown-list-wrapper.searching .multiselect-dropdown-list .option-group': {
        display: 'none'
      },
      '.multiselect-dropdown-search': {
        padding: '0.5rem 0.75rem',
        'border-radius': 'var(--border-radius--base)',
        border: 'solid 1px transparent',
        'border-bottom': 'solid 1px var(--color-border)',
        'font-size': 'inherit'
      },
      '.multiselect-dropdown-search::placeholder': {
        color: 'rgba(0, 0, 0, 0.7)',
        'font-size': '16px'
      },
      '.multiselect-dropdown-search:focus, .multiselect-dropdown-search:focus-visible': {
        outline: 'none',
        '--ms-ring-color': 'rgb(14 165 233)',
        'box-shadow': '0 0 0 calc(1px) var(--ms-ring-color)',
      },
      '.multiselect-dropdown-list': {
        'overflow-y': 'auto',
        'overflow-x': 'hidden',
        height: '100%',
        'max-height': `${config.maxHeight}` ?? '160px'
      },
      '.multiselect-dropdown-list::-webkit-scrollbar': {
        width: '4px'
      },
      '.multiselect-dropdown-list::-webkit-scrollbar-thumb': {
        'background-color': 'var(--color-background--option)',
        'border-radius': '1000px'
      },
      '.multiselect-dropdown-list .option, .multiselect-dropdown-list div > input, .multiselect-dropdown-list div > label':
      {
        cursor: 'pointer',
        'border-radius': 'var(--border-radius--base)'
      },
      '.multiselect-dropdown-list .option-group': {
        'font-weight': 'bold',
      },
      '.multiselect-dropdown-list .option': {
        'margin-left': '20px',
      },
      '.multiselect-dropdown-list div': {
        display: 'flex',
        'align-items': 'center',
        'justify-content': 'flex-start',
        'column-gap': '6px',
        padding: '1px 6px',
        margin: '6px 8px 6px 6px',
        transition: '100ms cubic-bezier(0.455, 0.03, 0.515, 0.955)'
      },
      '.multiselect-dropdown-list .option:hover': {
        'background-color': 'var(--color-background--option--hover)'
      },
      '.multiselect-dropdown-list-input': {
        height: '14px',
        width: '14px',
        border: 'solid 1px var(--color-text--grey)',
        margin: 0
      },
      '.multiselect-dropdown-all-selector': {
        'border-bottom': 'solid 1px var(--color-border)'
      }
    };
    const style = document.createElement('style');
    style.setAttribute('type', 'text/css');
    style.innerHTML = `${Object.keys(styles)
      .map(
        (selector) =>
          `${selector} { ${Object.keys(styles[selector])
            .map((property) => `${property}: ${styles[selector][property]}`)
            .join('; ')} }`
      )
      .join('\n')}`;
    document.head.appendChild(style);
  };

  config.useStyles && createStyles();
};

window.addEventListener('load', () => {
  MultiSelectDropdown(window.MultiSelectDropdownOptions);
});
