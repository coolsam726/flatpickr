import dayjs from 'dayjs/esm'
import advancedFormat from 'dayjs/plugin/advancedFormat'
import customParseFormat from 'dayjs/plugin/customParseFormat'
import localeData from 'dayjs/plugin/localeData'
import timezone from 'dayjs/plugin/timezone'
import utc from 'dayjs/plugin/utc'
import flatpickr from 'flatpickr'
import flatpickrLocales from 'flatpickr/dist/l10n'

import MonthSelect from 'flatpickr/dist/esm/plugins/monthSelect/index.js'
import WeekSelect from 'flatpickr/dist/esm/plugins/weekSelect/weekSelect.js'

dayjs.extend(advancedFormat)
dayjs.extend(customParseFormat)
dayjs.extend(localeData)
dayjs.extend(timezone)
dayjs.extend(utc)

window.dayjs = dayjs

function resolveLocale(localeConfig) {
  if (localeConfig === null || localeConfig === undefined) {
    return flatpickrLocales.en
  }

  if (typeof localeConfig === 'string') {
    return flatpickrLocales[localeConfig] ?? flatpickrLocales.en
  }

  if (typeof localeConfig === 'object') {
    const localeCode = localeConfig.locale ?? 'en'
    const baseLocale = flatpickrLocales[localeCode] ?? flatpickrLocales.en

    return {
      ...baseLocale,
      ...localeConfig,
    }
  }

  return flatpickrLocales.en
}

function resolveDayjsLocale(localeConfig) {
  if (typeof localeConfig === 'string') {
    return locales[localeConfig] ?? locales.en
  }

  if (typeof localeConfig === 'object' && localeConfig?.locale) {
    return locales[localeConfig.locale] ?? locales.en
  }

  return locales.en
}

function normalizeState(state) {
  if (state === null || state === undefined || state === '') {
    return null
  }

  return state
}

export default function flatpickrComponent(state, attrs) {
  const timezone = dayjs.tz.guess()

  return {
    state,
    attrs,
    timezone,

    fp: null,

    init: function () {
      this.initFlatpickr()

      this.$watch('state', (value) => {
        this.syncPickerFromState(value)
      })
    },

    initFlatpickr: function () {
      if (this.fp) {
        this.fp.destroy()
        this.fp = null
      }

      const localeConfig = this.attrs.locale ?? 'en'
      const customLocale = resolveLocale(localeConfig)
      const plugins = []

      if (this.attrs.monthPicker) {
        plugins.push(
          new MonthSelect({
            shorthand: this.attrs.monthPickerShorthand || false,
            dateFormat: this.attrs.dateFormat || 'Y-m',
            altFormat: this.attrs.altFormat || 'F Y',
          }),
        )
      } else if (this.attrs.weekPicker) {
        plugins.push(new WeekSelect({}))
      }

      const config = {
        disableMobile: true,
        initialDate: normalizeState(this.state),
        defaultDate: normalizeState(this.state),
        static: false,
        altInput: true,
        ...this.attrs,
        locale: customLocale,
        plugins,
        onChange: (selectedDates, dateStr) => {
          this.state = selectedDates.length === 0 ? null : dateStr
        },
        onClose: () => {
          if (!this.fp || !this.attrs.allowInput) {
            return
          }

          const inputValue = this.fp.altInput?.value ?? this.fp.input.value

          if (inputValue === '') {
            this.fp.clear()
            this.state = null

            return
          }

          const parsed = this.fp.parseDate(inputValue, this.fp.config.dateFormat)

          if (parsed) {
            this.fp.setDate(parsed, false)
            this.state = this.fp.input.value
          }
        },
      }

      dayjs.locale(resolveDayjsLocale(localeConfig))
      flatpickr.localize(customLocale)

      this.fp = flatpickr(this.$refs.input, config)

      if (this.state) {
        this.syncPickerFromState(this.state)
      }
    },

    syncPickerFromState: function (value) {
      if (!this.fp) {
        return
      }

      const normalized = normalizeState(value)

      if (normalized === null) {
        this.fp.clear()

        return
      }

      this.fp.setDate(normalized, false)
    },
  }
}

const locales = {
  ar: require('dayjs/locale/ar'),
  bs: require('dayjs/locale/bs'),
  ca: require('dayjs/locale/ca'),
  ckb: require('dayjs/locale/ku'),
  cs: require('dayjs/locale/cs'),
  cy: require('dayjs/locale/cy'),
  da: require('dayjs/locale/da'),
  de: require('dayjs/locale/de'),
  en: require('dayjs/locale/en'),
  es: require('dayjs/locale/es'),
  et: require('dayjs/locale/et'),
  fa: require('dayjs/locale/fa'),
  fi: require('dayjs/locale/fi'),
  fr: require('dayjs/locale/fr'),
  hi: require('dayjs/locale/hi'),
  hu: require('dayjs/locale/hu'),
  hy: require('dayjs/locale/hy-am'),
  id: require('dayjs/locale/id'),
  it: require('dayjs/locale/it'),
  ja: require('dayjs/locale/ja'),
  ka: require('dayjs/locale/ka'),
  km: require('dayjs/locale/km'),
  ku: require('dayjs/locale/ku'),
  lt: require('dayjs/locale/lt'),
  lv: require('dayjs/locale/lv'),
  ms: require('dayjs/locale/ms'),
  my: require('dayjs/locale/my'),
  nl: require('dayjs/locale/nl'),
  no: require('dayjs/locale/nb'),
  pl: require('dayjs/locale/pl'),
  pt_BR: require('dayjs/locale/pt-br'),
  pt_PT: require('dayjs/locale/pt'),
  ro: require('dayjs/locale/ro'),
  ru: require('dayjs/locale/ru'),
  sv: require('dayjs/locale/sv'),
  th: require('dayjs/locale/th'),
  tr: require('dayjs/locale/tr'),
  uk: require('dayjs/locale/uk'),
  vi: require('dayjs/locale/vi'),
  zh_CN: require('dayjs/locale/zh-cn'),
  zh_TW: require('dayjs/locale/zh-tw'),
}
