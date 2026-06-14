import dayjs from 'dayjs/esm'
import advancedFormat from 'dayjs/plugin/advancedFormat'
import customParseFormat from 'dayjs/plugin/customParseFormat'
import localeData from 'dayjs/plugin/localeData'
import timezone from 'dayjs/plugin/timezone'
import utc from 'dayjs/plugin/utc'
import flatpickr from 'flatpickr'
import flatpickrLocalesImport from 'flatpickr/dist/l10n'

const flatpickrLocales = flatpickrLocalesImport.default ?? flatpickrLocalesImport

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

    const { locale: _locale, ...overrides } = localeConfig

    return {
      ...baseLocale,
      ...overrides,
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

function parseConstraintValue(value) {
  if (value === null || value === undefined || value === '') {
    return null
  }

  return value
}

function isRangeMode(attrs) {
  return attrs.mode === 'range' || attrs.rangePicker === true
}

function formatSelectedDates(fp, selectedDates, attrs) {
  if (!selectedDates?.length || !fp) {
    return null
  }

  const format = fp.config.dateFormat ?? attrs.dateFormat ?? 'Y-m-d'
  const separator = isRangeMode(attrs)
    ? (fp.l10n?.rangeSeparator ?? attrs.rangeSeparator ?? ' to ')
    : (attrs.conjunction ?? ', ')

  return selectedDates.map((date) => fp.formatDate(date, format)).join(separator)
}

function setRangeDatesPreservingOrder(fp, dates, triggerChange = false) {
  fp.selectedDates = dates.filter(Boolean)
  fp.latestSelectedDateObj = fp.selectedDates[fp.selectedDates.length - 1] ?? undefined
  fp.redraw()
  fp.updateValue(triggerChange)
}

export default function flatpickrComponent(state, attrs) {
  const timezone = dayjs.tz.guess()

  return {
    state,
    attrs,
    timezone,

    fp: null,
    visibilityObserver: null,
    isPickerUpdate: false,

    init: function () {
      this.initWhenVisible()

      this.$watch('state', (value) => {
        if (this.isPickerUpdate) {
          return
        }

        this.syncPickerFromState(value)
      })

      this.registerModalListeners()
    },

    registerModalListeners: function () {
      const initIfNeeded = () => {
        if (!this.fp && this.isElementVisible()) {
          this.initFlatpickr()
        }
      }

      ;['modal-opened', 'ax-modal-opened', 'opened-form-component-action-modal'].forEach((eventName) => {
        window.addEventListener(eventName, initIfNeeded)
      })
    },

    isElementVisible: function () {
      return this.$el && this.$el.offsetParent !== null
    },

    initWhenVisible: function () {
      if (this.isElementVisible()) {
        this.initFlatpickr()

        return
      }

      if (typeof IntersectionObserver === 'undefined') {
        return
      }

      this.visibilityObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && !this.fp) {
            this.initFlatpickr()
            this.visibilityObserver?.disconnect()
          }
        })
      })

      this.visibilityObserver.observe(this.$el)
    },

    setPickerState: function (selectedDates) {
      this.isPickerUpdate = true
      this.state =
        selectedDates.length === 0 ? null : formatSelectedDates(this.fp, selectedDates, this.attrs)

      this.$nextTick(() => {
        this.isPickerUpdate = false
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

      const minDate = parseConstraintValue(this.$refs.minDate?.value ?? this.attrs.minDate)
      const maxDate = parseConstraintValue(this.$refs.maxDate?.value ?? this.attrs.maxDate)

      let disabledDates = this.attrs.disable ?? []

      if (this.$refs.disabledDates?.value) {
        try {
          disabledDates = JSON.parse(this.$refs.disabledDates.value) ?? disabledDates
        } catch (error) {
          disabledDates = this.attrs.disable ?? []
        }
      }

      const config = {
        disableMobile: true,
        initialDate: normalizeState(this.state),
        defaultDate: normalizeState(this.state),
        static: false,
        altInput: true,
        ...this.attrs,
        minDate,
        maxDate,
        disable: disabledDates,
        locale: customLocale,
        plugins,
        onChange: (selectedDates, dateStr) => {
          if (this.attrs.yearPicker && selectedDates.length > 0) {
            this.state = String(selectedDates[0].getFullYear())

            return
          }

          if (selectedDates.length === 0) {
            this.setPickerState([])

            return
          }

          this.setPickerState(selectedDates)
        },
        onClose: () => {
          this.syncStateFromPicker()
        },
      }

      dayjs.locale(resolveDayjsLocale(localeConfig))
      flatpickr.localize(customLocale)

      this.fp = flatpickr(this.$refs.input, config)

      this.bindManualInputEvents()

      if (this.state) {
        this.syncPickerFromState(this.state)
      }
    },

    bindManualInputEvents: function () {
      if (!this.fp) {
        return
      }

      const inputs = [this.fp.altInput, this.fp.input].filter(Boolean)

      inputs.forEach((input) => {
        input.addEventListener('blur', () => {
          this.commitManualInput()
        })
      })
    },

    syncStateFromPicker: function () {
      if (!this.fp) {
        return
      }

      if (isRangeMode(this.attrs)) {
        this.setPickerState(this.fp.selectedDates)

        return
      }

      this.commitManualInput()
    },

    commitManualInput: function () {
      if (!this.fp) {
        return
      }

      if (isRangeMode(this.attrs)) {
        this.setPickerState(this.fp.selectedDates)

        return
      }

      const inputValue = this.fp.altInput?.value ?? this.fp.input.value

      if (inputValue === '') {
        this.fp.clear()
        this.state = null

        return
      }

      if (!this.attrs.allowInput && !this.attrs.timePicker) {
        return
      }

      const parsed = this.fp.parseDate(inputValue, this.fp.config.dateFormat)

      if (!parsed) {
        return
      }

      this.fp.setDate(parsed, false)

      if (this.attrs.yearPicker) {
        this.state = String(parsed.getFullYear())

        return
      }

      this.state = this.fp.input.value
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

      if (isRangeMode(this.attrs) && this.attrs.enableTime && typeof normalized === 'string') {
        const separator = this.fp.l10n?.rangeSeparator ?? this.attrs.rangeSeparator ?? ' to '

        if (normalized.includes(separator)) {
          const parts = normalized.split(separator).map((part) => part.trim())

          if (parts.length === 2) {
            const format = this.fp.config.dateFormat
            const dates = parts
              .map((part) => this.fp.parseDate(part, format))
              .filter(Boolean)

            if (dates.length === 2) {
              setRangeDatesPreservingOrder(this.fp, dates)

              return
            }
          }
        }
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
