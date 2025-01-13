/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./client/src/boot/cms/index.js":
/*!**************************************!*\
  !*** ./client/src/boot/cms/index.js ***!
  \**************************************/
/***/ (function(__unused_webpack_module, __unused_webpack_exports, __webpack_require__) {

"use strict";


var _registerComponents = _interopRequireDefault(__webpack_require__(/*! ./registerComponents */ "./client/src/boot/cms/registerComponents.js"));
var _registerReducers = _interopRequireDefault(__webpack_require__(/*! ./registerReducers */ "./client/src/boot/cms/registerReducers.js"));
var _registerTransformations = _interopRequireDefault(__webpack_require__(/*! ./registerTransformations */ "./client/src/boot/cms/registerTransformations.js"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
window.document.addEventListener('DOMContentLoaded', () => {
  (0, _registerComponents.default)();
  (0, _registerReducers.default)();
  (0, _registerTransformations.default)();
});

/***/ }),

/***/ "./client/src/boot/cms/registerComponents.js":
/*!***************************************************!*\
  !*** ./client/src/boot/cms/registerComponents.js ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _Injector = _interopRequireDefault(__webpack_require__(/*! lib/Injector */ "lib/Injector"));
var _Register = _interopRequireDefault(__webpack_require__(/*! components/Register */ "./client/src/components/Register.js"));
var _RegisteredMFAMethodListField = _interopRequireDefault(__webpack_require__(/*! components/FormField/RegisteredMFAMethodListField/RegisteredMFAMethodListField */ "./client/src/components/FormField/RegisteredMFAMethodListField/RegisteredMFAMethodListField.js"));
var _registerComponents = _interopRequireDefault(__webpack_require__(/*! ../registerComponents */ "./client/src/boot/registerComponents.js"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
var _default = () => {
  (0, _registerComponents.default)();
  _Injector.default.component.registerMany({
    MFARegister: _Register.default,
    RegisteredMFAMethodListField: _RegisteredMFAMethodListField.default
  });
};
exports["default"] = _default;

/***/ }),

/***/ "./client/src/boot/cms/registerReducers.js":
/*!*************************************************!*\
  !*** ./client/src/boot/cms/registerReducers.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _Injector = _interopRequireDefault(__webpack_require__(/*! lib/Injector */ "lib/Injector"));
var _reducer = _interopRequireDefault(__webpack_require__(/*! state/mfaAdministration/reducer */ "./client/src/state/mfaAdministration/reducer.js"));
var _registerReducers = _interopRequireDefault(__webpack_require__(/*! ../registerReducers */ "./client/src/boot/registerReducers.js"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
var _default = () => {
  (0, _registerReducers.default)();
  _Injector.default.reducer.register('mfaAdministration', _reducer.default);
};
exports["default"] = _default;

/***/ }),

/***/ "./client/src/boot/cms/registerTransformations.js":
/*!********************************************************!*\
  !*** ./client/src/boot/cms/registerTransformations.js ***!
  \********************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _Injector = _interopRequireDefault(__webpack_require__(/*! lib/Injector */ "lib/Injector"));
var _SudoMode = _interopRequireDefault(__webpack_require__(/*! containers/SudoMode/SudoMode */ "containers/SudoMode/SudoMode"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
var _default = () => {
  _Injector.default.transform('apply-sudo-mode-to-mfa', updater => {
    updater.component('RegisteredMFAMethodListField', _SudoMode.default);
  });
};
exports["default"] = _default;

/***/ }),

/***/ "./client/src/boot/registerComponents.js":
/*!***********************************************!*\
  !*** ./client/src/boot/registerComponents.js ***!
  \***********************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _Register = _interopRequireDefault(__webpack_require__(/*! components/BackupCodes/Register */ "./client/src/components/BackupCodes/Register.js"));
var _Verify = _interopRequireDefault(__webpack_require__(/*! components/BackupCodes/Verify */ "./client/src/components/BackupCodes/Verify.js"));
var _Injector = _interopRequireDefault(__webpack_require__(/*! lib/Injector */ "lib/Injector"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
var _default = () => {
  _Injector.default.component.registerMany({
    BackupCodeRegister: _Register.default,
    BackupCodeVerify: _Verify.default
  });
};
exports["default"] = _default;

/***/ }),

/***/ "./client/src/boot/registerReducers.js":
/*!*********************************************!*\
  !*** ./client/src/boot/registerReducers.js ***!
  \*********************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _Injector = _interopRequireDefault(__webpack_require__(/*! lib/Injector */ "lib/Injector"));
var _reducer = _interopRequireDefault(__webpack_require__(/*! state/mfaRegister/reducer */ "./client/src/state/mfaRegister/reducer.js"));
var _reducer2 = _interopRequireDefault(__webpack_require__(/*! state/mfaVerify/reducer */ "./client/src/state/mfaVerify/reducer.js"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
var _default = () => {
  _Injector.default.reducer.register('mfaRegister', _reducer.default);
  _Injector.default.reducer.register('mfaVerify', _reducer2.default);
};
exports["default"] = _default;

/***/ }),

/***/ "./client/src/components/BackupCodes/Register.js":
/*!*******************************************************!*\
  !*** ./client/src/components/BackupCodes/Register.js ***!
  \*******************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _react = _interopRequireWildcard(__webpack_require__(/*! react */ "react"));
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
var _printd = _interopRequireDefault(__webpack_require__(/*! printd */ "./node_modules/printd/index.js"));
var _reactCopyToClipboard = __webpack_require__(/*! react-copy-to-clipboard */ "./node_modules/react-copy-to-clipboard/lib/index.js");
var _formatCode = __webpack_require__(/*! lib/formatCode */ "./client/src/lib/formatCode.js");
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
function _getRequireWildcardCache(e) { if ("function" != typeof WeakMap) return null; var r = new WeakMap(), t = new WeakMap(); return (_getRequireWildcardCache = function (e) { return e ? t : r; })(e); }
function _interopRequireWildcard(e, r) { if (!r && e && e.__esModule) return e; if (null === e || "object" != typeof e && "function" != typeof e) return { default: e }; var t = _getRequireWildcardCache(r); if (t && t.has(e)) return t.get(e); var n = { __proto__: null }, a = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var u in e) if ("default" !== u && {}.hasOwnProperty.call(e, u)) { var i = a ? Object.getOwnPropertyDescriptor(e, u) : null; i && (i.get || i.set) ? Object.defineProperty(n, u, i) : n[u] = e[u]; } return n.default = e, t && t.set(e, n), n; }
function Register(props) {
  const {
    codes,
    method,
    onCompleteRegistration,
    copyFeedbackDuration
  } = props;
  const [recentlyCopied, setRecentlyCopied] = (0, _react.useState)(false);
  const copyMessageTimeout = (0, _react.useRef)(null);
  const printRef = (0, _react.useRef)(null);
  const i18n = window.ss.i18n;
  function getFormattedCodes() {
    return codes.map(code => (0, _formatCode.formatCode)(code));
  }
  function handlePrint(event) {
    event.preventDefault();
    new _printd.default().print(printRef, ['body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif }']);
  }
  function handleCopy(event) {
    event.preventDefault();
    setRecentlyCopied(true);
    if (copyMessageTimeout.current) {
      clearTimeout(copyMessageTimeout.current);
    }
    copyMessageTimeout.current = setTimeout(() => {
      setRecentlyCopied(false);
    }, copyFeedbackDuration);
  }
  function renderCodes() {
    return _react.default.createElement("pre", {
      ref: printRef,
      className: "mfa-register-backup-codes__code-grid"
    }, getFormattedCodes().map(code => _react.default.createElement("div", {
      key: code
    }, code)));
  }
  function renderDescription() {
    const {
      supportLink,
      supportText
    } = method;
    return _react.default.createElement("p", null, i18n._t('MFABackupCodesRegister.DESCRIPTION', 'Recovery codes enable you to log into your account in the event your primary ' + 'authentication is not available. Each code can only be used once. Store these codes ' + 'somewhere safe, as they will not be viewable after leaving this page.'), "\xA0", supportLink && _react.default.createElement("a", {
      href: supportLink,
      target: "_blank",
      rel: "noopener noreferrer"
    }, supportText || i18n._t('MFARegister.RECOVERY_HELP', 'Learn more about recovery codes.')));
  }
  function renderPrintAction() {
    return _react.default.createElement("button", {
      type: "button",
      onClick: handlePrint,
      className: "btn btn-link"
    }, i18n._t('MFABackupCodesRegister.PRINT', 'Print codes'));
  }
  function renderDownloadAction() {
    const {
      Blob,
      URL,
      navigator
    } = window;
    const filename = `${method.name}.txt`;
    const codesText = codes.join('\r\n');
    const codesBlob = new Blob([codesText], {
      type: 'text/plain;charset=UTF-8'
    });
    const codesURL = URL.createObjectURL(codesBlob);
    const supportInternetExplorer = e => {
      if (navigator.msSaveBlob) {
        e.preventDefault();
        navigator.msSaveBlob(codesBlob, filename);
      }
    };
    return _react.default.createElement("a", {
      download: filename,
      href: codesURL,
      className: "btn btn-link",
      onClick: supportInternetExplorer
    }, i18n._t('MFABackupCodesRegister.DOWNLOAD', 'Download'));
  }
  function renderCopyAction() {
    const label = recentlyCopied ? i18n._t('MFABackupCodesRegister.COPY_RECENT', 'Copied!') : i18n._t('MFABackupCodesRegister.COPY', 'Copy codes');
    return _react.default.createElement(_reactCopyToClipboard.CopyToClipboard, {
      text: codes.join('\n')
    }, _react.default.createElement("button", {
      type: "button",
      className: "mfa-register-backup-codes__copy-to-clipboard btn btn-link",
      onClick: () => handleCopy()
    }, label));
  }
  return _react.default.createElement("div", {
    className: "mfa-register-backup-codes__container"
  }, renderDescription(), renderCodes(), _react.default.createElement("div", {
    className: "mfa-register-backup-codes__helper-links"
  }, renderPrintAction(), renderDownloadAction(), renderCopyAction()), _react.default.createElement("button", {
    className: "btn btn-primary",
    onClick: () => onCompleteRegistration()
  }, i18n._t('MFABackupCodesRegister.FINISH', 'Finish')));
}
Register.propTypes = {
  codes: _propTypes.default.arrayOf(_propTypes.default.string),
  copyFeedbackDuration: _propTypes.default.number
};
Register.defaultProps = {
  copyFeedbackDuration: 3000
};
var _default = exports["default"] = Register;

/***/ }),

/***/ "./client/src/components/BackupCodes/Verify.js":
/*!*****************************************************!*\
  !*** ./client/src/components/BackupCodes/Verify.js ***!
  \*****************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _react = _interopRequireWildcard(__webpack_require__(/*! react */ "react"));
var _classnames = _interopRequireDefault(__webpack_require__(/*! classnames */ "classnames"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
function _getRequireWildcardCache(e) { if ("function" != typeof WeakMap) return null; var r = new WeakMap(), t = new WeakMap(); return (_getRequireWildcardCache = function (e) { return e ? t : r; })(e); }
function _interopRequireWildcard(e, r) { if (!r && e && e.__esModule) return e; if (null === e || "object" != typeof e && "function" != typeof e) return { default: e }; var t = _getRequireWildcardCache(r); if (t && t.has(e)) return t.get(e); var n = { __proto__: null }, a = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var u in e) if ("default" !== u && {}.hasOwnProperty.call(e, u)) { var i = a ? Object.getOwnPropertyDescriptor(e, u) : null; i && (i.get || i.set) ? Object.defineProperty(n, u, i) : n[u] = e[u]; } return n.default = e, t && t.set(e, n), n; }
function Verify(props) {
  const {
    error,
    graphic,
    method,
    name,
    moreOptionsControl,
    onCompleteVerification
  } = props;
  const [value, setValue] = (0, _react.useState)('');
  const codeInput = (0, _react.useRef)(null);
  const i18n = window.ss.i18n;
  (0, _react.useEffect)(() => {
    if (codeInput.current) {
      codeInput.current.focus();
    }
  }, [codeInput]);
  function handleChange(event) {
    setValue(event.target.value);
  }
  function handleCompleteVerification(event) {
    event.preventDefault();
    onCompleteVerification({
      code: value
    });
  }
  function renderControls() {
    return _react.default.createElement("ul", {
      className: "mfa-action-list mfa-action-list--backup-codes"
    }, _react.default.createElement("li", {
      className: "mfa-action-list__item"
    }, _react.default.createElement("button", {
      className: "btn btn-primary",
      disabled: value.length === 0,
      onClick: handleCompleteVerification
    }, i18n._t('MFABackupCodesVerify.NEXT', 'Next'))), moreOptionsControl && _react.default.createElement("li", {
      className: "mfa-action-list__item"
    }, moreOptionsControl));
  }
  function renderDescription() {
    return _react.default.createElement("p", null, i18n._t('MFABackupCodesVerify.DESCRIPTION', 'Use one of the recovery codes you received'), "\xA0", method && method.supportLink && _react.default.createElement("a", {
      href: method.supportLink,
      target: "_blank",
      rel: "noopener noreferrer"
    }, i18n._t('MFARegister.RECOVERY_HELP', 'How to use recovery codes.')));
  }
  function renderInput() {
    const label = i18n._t('MFABackupCodesVerify.LABEL', 'Enter recovery code');
    const formGroupClasses = (0, _classnames.default)('mfa-verify-backup-codes__input-container', {
      'has-error': !!error
    });
    return _react.default.createElement("div", {
      className: formGroupClasses
    }, _react.default.createElement("label", {
      htmlFor: "backup-code",
      className: "control-label"
    }, label), _react.default.createElement("input", {
      className: "mfa-verify-backup-codes__input text form-control",
      type: "text",
      placeholder: label,
      id: "backup-code",
      ref: codeInput,
      onChange: handleChange
    }), error && _react.default.createElement("div", {
      className: "help-block"
    }, error));
  }
  return _react.default.createElement("form", {
    className: "mfa-verify-backup-codes__container"
  }, _react.default.createElement("div", {
    className: "mfa-verify-backup-codes__content"
  }, renderDescription(), renderInput()), _react.default.createElement("div", {
    className: "mfa-verify-backup-codes__image-holder"
  }, _react.default.createElement("img", {
    className: "mfa-verify-backup-codes__image",
    src: graphic,
    alt: name
  })), renderControls());
}
var _default = exports["default"] = Verify;

/***/ }),

/***/ "./client/src/components/FormField/RegisteredMFAMethodListField/AccountResetUI.js":
/*!****************************************************************************************!*\
  !*** ./client/src/components/FormField/RegisteredMFAMethodListField/AccountResetUI.js ***!
  \****************************************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
var _react = _interopRequireWildcard(__webpack_require__(/*! react */ "react"));
var _Config = _interopRequireDefault(__webpack_require__(/*! lib/Config */ "lib/Config"));
var _api = _interopRequireDefault(__webpack_require__(/*! lib/api */ "./client/src/lib/api.js"));
var _reactstrapConfirm = _interopRequireDefault(__webpack_require__(/*! reactstrap-confirm */ "./node_modules/reactstrap-confirm/dist/index.js"));
var _LoadingIndicator = _interopRequireDefault(__webpack_require__(/*! ../../LoadingIndicator */ "./client/src/components/LoadingIndicator.js"));
var _CircleDash = _interopRequireDefault(__webpack_require__(/*! ../../Icons/CircleDash */ "./client/src/components/Icons/CircleDash.js"));
var _CircleTick = _interopRequireDefault(__webpack_require__(/*! ../../Icons/CircleTick */ "./client/src/components/Icons/CircleTick.js"));
var _regeneratorRuntime = _interopRequireDefault(__webpack_require__(/*! regenerator-runtime */ "./node_modules/regenerator-runtime/runtime.js"));
var _en = _interopRequireDefault(__webpack_require__(/*! ../../../../lang/src/en.json */ "./client/lang/src/en.json"));
function _getRequireWildcardCache(e) { if ("function" != typeof WeakMap) return null; var r = new WeakMap(), t = new WeakMap(); return (_getRequireWildcardCache = function (e) { return e ? t : r; })(e); }
function _interopRequireWildcard(e, r) { if (!r && e && e.__esModule) return e; if (null === e || "object" != typeof e && "function" != typeof e) return { default: e }; var t = _getRequireWildcardCache(r); if (t && t.has(e)) return t.get(e); var n = { __proto__: null }, a = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var u in e) if ("default" !== u && {}.hasOwnProperty.call(e, u)) { var i = a ? Object.getOwnPropertyDescriptor(e, u) : null; i && (i.get || i.set) ? Object.defineProperty(n, u, i) : n[u] = e[u]; } return n.default = e, t && t.set(e, n), n; }
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
function AccountResetUI(props) {
  const {
    LoadingIndicatorComponent,
    resetEndpoint
  } = props;
  const [complete, setComplete] = (0, _react.useState)(false);
  const [failed, setFailed] = (0, _react.useState)(false);
  const [submitting, setSubmitting] = (0, _react.useState)(false);
  const i18n = window.ss.i18n;
  async function handleSendReset() {
    const confirmMessage = i18n._t('MultiFactorAuthentication.ACCOUNT_RESET_CONFIRMATION', _en.default['MultiFactorAuthentication.ACCOUNT_RESET_CONFIRMATION']);
    const confirmTitle = i18n._t('MultiFactorAuthentication.CONFIRMATION_TITLE', _en.default['MultiFactorAuthentication.CONFIRMATION_TITLE']);
    const buttonLabel = i18n._t('MultiFactorAuthentication.ACCOUNT_RESET_CONFIRMATION_BUTTON', _en.default['MultiFactorAuthentication.ACCOUNT_RESET_CONFIRMATION_BUTTON']);
    if (!(await (0, _reactstrapConfirm.default)({
      title: confirmTitle,
      message: confirmMessage,
      confirmText: buttonLabel
    }))) {
      return;
    }
    setSubmitting(true);
    const body = JSON.stringify({
      csrf_token: _Config.default.get('SecurityID')
    });
    (0, _api.default)(resetEndpoint, 'POST', body).then(response => response.json()).then(output => {
      setComplete(true);
      setFailed(!!output.error);
      setSubmitting(false);
    }).catch(() => {
      setComplete(true);
      setFailed(true);
      setSubmitting(false);
    });
  }
  function renderAction() {
    if (complete || submitting) {
      return null;
    }
    return _react.default.createElement("p", {
      className: "account-reset-action"
    }, _react.default.createElement("button", {
      className: "btn btn-outline-secondary",
      disabled: !resetEndpoint,
      onClick: () => handleSendReset(),
      type: "button"
    }, i18n._t('MultiFactorAuthentication.ACCOUNT_RESET_ACTION', _en.default['MultiFactorAuthentication.ACCOUNT_RESET_ACTION'])));
  }
  function renderSending() {
    return _react.default.createElement("p", {
      className: "account-reset-action account-reset-action--sending"
    }, _react.default.createElement("span", {
      className: "account-reset-action__icon"
    }, _react.default.createElement(LoadingIndicatorComponent, {
      size: "32px"
    })), _react.default.createElement("span", {
      className: "account-reset-action__message"
    }, i18n._t('MultiFactorAuthentication.ACCOUNT_RESET_SENDING', _en.default['MultiFactorAuthentication.ACCOUNT_RESET_SENDING'])));
  }
  function renderFailure() {
    return _react.default.createElement("p", {
      className: "account-reset-action account-reset-action--failure"
    }, _react.default.createElement("span", {
      className: "account-reset-action__icon"
    }, _react.default.createElement(_CircleDash.default, {
      size: "32px"
    })), _react.default.createElement("span", {
      className: "account-reset-action__message"
    }, i18n._t('MultiFactorAuthentication.ACCOUNT_RESET_SENDING', _en.default['MultiFactorAuthentication.ACCOUNT_RESET_SENDING_FAILURE'])));
  }
  function renderSuccess() {
    return _react.default.createElement("p", {
      className: "account-reset-action account-reset-action--success"
    }, _react.default.createElement("span", {
      className: "account-reset-action__icon"
    }, _react.default.createElement(_CircleTick.default, {
      size: "32px"
    })), _react.default.createElement("span", {
      className: "account-reset-action__message"
    }, i18n._t('MultiFactorAuthentication.ACCOUNT_RESET_SENDING_SUCCESS', _en.default['MultiFactorAuthentication.ACCOUNT_RESET_SENDING_SUCCESS'])));
  }
  function renderStatusMessage() {
    if (submitting) {
      return renderSending();
    }
    if (!complete) {
      return null;
    }
    return failed ? renderFailure() : renderSuccess();
  }
  return _react.default.createElement("div", {
    className: "account-reset"
  }, _react.default.createElement("h5", {
    className: "account-re'set__title"
  }, i18n._t('MultiFactorAuthentication.ACCOUNT_RESET_TITLE', _en.default['MultiFactorAuthentication.ACCOUNT_RESET_TITLE'])), _react.default.createElement("p", {
    className: "account-reset__description"
  }, i18n._t('MultiFactorAuthentication.ACCOUNT_RESET_DESCRIPTION', _en.default['MultiFactorAuthentication.ACCOUNT_RESET_DESCRIPTION'])), renderAction(), renderStatusMessage());
}
AccountResetUI.propTypes = {
  resetEndpoint: _propTypes.default.string,
  LoadingIndicatorComponent: _propTypes.default.oneOfType([_propTypes.default.object, _propTypes.default.func])
};
AccountResetUI.defaultProps = {
  LoadingIndicatorComponent: _LoadingIndicator.default
};
var _default = exports["default"] = AccountResetUI;

/***/ }),

/***/ "./client/src/components/FormField/RegisteredMFAMethodListField/MethodListItem.js":
/*!****************************************************************************************!*\
  !*** ./client/src/components/FormField/RegisteredMFAMethodListField/MethodListItem.js ***!
  \****************************************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));
var _reactstrapConfirm = _interopRequireDefault(__webpack_require__(/*! reactstrap-confirm */ "./node_modules/reactstrap-confirm/dist/index.js"));
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
var _classnames = _interopRequireDefault(__webpack_require__(/*! classnames */ "classnames"));
var _moment = _interopRequireDefault(__webpack_require__(/*! moment */ "moment"));
var _registeredMethod = _interopRequireDefault(__webpack_require__(/*! types/registeredMethod */ "./client/src/types/registeredMethod.js"));
var _Remove = _interopRequireDefault(__webpack_require__(/*! ./MethodListItem/Remove */ "./client/src/components/FormField/RegisteredMFAMethodListField/MethodListItem/Remove.js"));
var _Reset = _interopRequireDefault(__webpack_require__(/*! ./MethodListItem/Reset */ "./client/src/components/FormField/RegisteredMFAMethodListField/MethodListItem/Reset.js"));
var _SetDefault = _interopRequireDefault(__webpack_require__(/*! ./MethodListItem/SetDefault */ "./client/src/components/FormField/RegisteredMFAMethodListField/MethodListItem/SetDefault.js"));
var _en = _interopRequireDefault(__webpack_require__(/*! ../../../../lang/src/en.json */ "./client/lang/src/en.json"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
function MethodListItem(props) {
  const {
    allAvailableMethods,
    backupMethod,
    canRemove,
    canReset,
    className,
    createdDate,
    endpoints,
    isBackupMethod,
    isDefaultMethod,
    method,
    RemoveComponent,
    resources,
    SetDefaultComponent,
    tag: Tag
  } = props;
  const i18n = window.ss.i18n;
  function getStatusMessage() {
    if (isDefaultMethod) {
      return i18n._t('MultiFactorAuthentication.DEFAULT_REGISTERED', _en.default['MultiFactorAuthentication.DEFAULT_REGISTERED']);
    }
    if (isBackupMethod) {
      return i18n._t('MultiFactorAuthentication.BACKUP_REGISTERED', _en.default['MultiFactorAuthentication.BACKUP_REGISTERED']);
    }
    return i18n._t('MultiFactorAuthentication.REGISTERED', _en.default['MultiFactorAuthentication.REGISTERED']);
  }
  function renderRemove() {
    if (!canRemove) {
      return null;
    }
    return _react.default.createElement(RemoveComponent, {
      method: method,
      backupMethod: backupMethod,
      endpoints: endpoints
    });
  }
  function renderReset() {
    if (!canReset) {
      return null;
    }
    const resetProps = {
      method,
      allAvailableMethods,
      backupMethod,
      endpoints,
      resources
    };
    if (isBackupMethod) {
      const confirmMessage = i18n._t('MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION', _en.default['MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION']);
      const confirmTitle = i18n._t('MultiFactorAuthentication.CONFIRMATION_TITLE', _en.default['MultiFactorAuthentication.CONFIRMATION_TITLE']);
      const buttonLabel = i18n._t('MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION_BUTTON', _en.default['MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION_BUTTON']);
      resetProps.onReset = async callback => {
        if (!(await (0, _reactstrapConfirm.default)({
          title: confirmTitle,
          message: confirmMessage,
          confirmText: buttonLabel
        }))) {
          return;
        }
        callback();
      };
    }
    return _react.default.createElement(_Reset.default, resetProps);
  }
  function renderSetAsDefault() {
    if (isDefaultMethod || isBackupMethod) {
      return null;
    }
    return _react.default.createElement(SetDefaultComponent, {
      method: method,
      endpoints: endpoints
    });
  }
  function renderControls() {
    if (!canRemove && !canReset) {
      return null;
    }
    return _react.default.createElement("div", null, renderRemove(), renderReset(), renderSetAsDefault());
  }
  function renderNameAndStatus() {
    const statusMessage = getStatusMessage();
    _moment.default.locale(i18n.detectLocale());
    return i18n.inject(statusMessage, {
      method: method.name,
      date: (0, _moment.default)(createdDate).format('L')
    });
  }
  const classes = (0, _classnames.default)(className, 'registered-method-list-item');
  return _react.default.createElement(Tag, {
    className: classes
  }, renderNameAndStatus(), renderControls());
}
MethodListItem.propTypes = {
  method: _registeredMethod.default.isRequired,
  isDefaultMethod: _propTypes.default.bool,
  isBackupMethod: _propTypes.default.bool,
  canRemove: _propTypes.default.bool,
  canReset: _propTypes.default.bool,
  onRemove: _propTypes.default.func,
  onReset: _propTypes.default.func,
  createdDate: _propTypes.default.string,
  className: _propTypes.default.string,
  tag: _propTypes.default.string,
  RemoveComponent: _propTypes.default.oneOfType([_propTypes.default.object, _propTypes.default.func]),
  SetDefaultComponent: _propTypes.default.oneOfType([_propTypes.default.object, _propTypes.default.func])
};
MethodListItem.defaultProps = {
  isDefaultMethod: false,
  isBackupMethod: false,
  canRemove: false,
  canReset: false,
  tag: 'li',
  RemoveComponent: _Remove.default,
  SetDefaultComponent: _SetDefault.default
};
var _default = exports["default"] = MethodListItem;

/***/ }),

/***/ "./client/src/components/FormField/RegisteredMFAMethodListField/MethodListItem/Remove.js":
/*!***********************************************************************************************!*\
  !*** ./client/src/components/FormField/RegisteredMFAMethodListField/MethodListItem/Remove.js ***!
  \***********************************************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
var _reactRedux = __webpack_require__(/*! react-redux */ "react-redux");
var _reactstrapConfirm = _interopRequireDefault(__webpack_require__(/*! reactstrap-confirm */ "./node_modules/reactstrap-confirm/dist/index.js"));
var _Config = _interopRequireDefault(__webpack_require__(/*! lib/Config */ "lib/Config"));
var _api = _interopRequireDefault(__webpack_require__(/*! lib/api */ "./client/src/lib/api.js"));
var _actions = __webpack_require__(/*! state/mfaRegister/actions */ "./client/src/state/mfaRegister/actions.js");
var _actions2 = __webpack_require__(/*! state/mfaAdministration/actions */ "./client/src/state/mfaAdministration/actions.js");
var _registeredMethod = _interopRequireDefault(__webpack_require__(/*! types/registeredMethod */ "./client/src/types/registeredMethod.js"));
var _en = _interopRequireDefault(__webpack_require__(/*! ../../../../../lang/src/en.json */ "./client/lang/src/en.json"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
const Remove = props => {
  const {
    backupMethod,
    endpoints,
    defaultMethod,
    method,
    onRemove,
    registeredMethods,
    onDeregisterMethod,
    onAddAvailableMethod,
    onSetDefaultMethod
  } = props;
  const remove = endpoints.remove;
  const i18n = window.ss.i18n;
  const handleRemove = async () => {
    const confirmMessage = i18n._t('MultiFactorAuthentication.DELETE_CONFIRMATION', _en.default['MultiFactorAuthentication.DELETE_CONFIRMATION']);
    const confirmTitle = i18n._t('MultiFactorAuthentication.CONFIRMATION_TITLE', _en.default['MultiFactorAuthentication.CONFIRMATION_TITLE']);
    const buttonLabel = i18n._t('MultiFactorAuthentication.DELETE_CONFIRMATION_BUTTON', _en.default['MultiFactorAuthentication.DELETE_CONFIRMATION_BUTTON']);
    if (!(await (0, _reactstrapConfirm.default)({
      title: confirmTitle,
      message: confirmMessage,
      confirmText: buttonLabel
    }))) {
      return;
    }
    const token = _Config.default.get('SecurityID');
    const endpoint = `${remove.replace('{urlSegment}', method.urlSegment)}?SecurityID=${token}`;
    (0, _api.default)(endpoint, 'DELETE').then(response => response.json().then(json => {
      if (response.status === 200) {
        onDeregisterMethod(method);
        onAddAvailableMethod(json.availableMethod);
        if (method.urlSegment === defaultMethod) {
          onSetDefaultMethod(null);
        }
        if (!json.hasBackupMethod && backupMethod && registeredMethods.find(candidate => candidate.urlSegment === backupMethod.urlSegment)) {
          onDeregisterMethod(backupMethod);
        }
        return;
      }
      const message = json.errors && ` Errors: \n - ${json.errors.join('\n -')}` || '';
      throw Error(`Could not delete method. Error code ${response.status}.${message}`);
    }));
  };
  return _react.default.createElement("button", {
    className: "registered-method-list-item__control",
    type: "button",
    onClick: onRemove ? onRemove(handleRemove) : handleRemove
  }, i18n._t('MultiFactorAuthentication.REMOVE_METHOD', _en.default['MultiFactorAuthentication.REMOVE_METHOD']));
};
Remove.propTypes = {
  method: _registeredMethod.default.isRequired,
  onRemove: _propTypes.default.func,
  defaultMethod: _propTypes.default.string.isRequired,
  registeredMethods: _propTypes.default.arrayOf(_registeredMethod.default).isRequired,
  onDeregisterMethod: _propTypes.default.func.isRequired,
  onAddAvailableMethod: _propTypes.default.func.isRequired,
  onSetDefaultMethod: _propTypes.default.func.isRequired
};
var _default = exports["default"] = (0, _reactRedux.connect)(_ref => {
  let {
    mfaAdministration: {
      defaultMethod,
      registeredMethods
    }
  } = _ref;
  return {
    defaultMethod,
    registeredMethods
  };
}, dispatch => ({
  onDeregisterMethod: method => {
    dispatch((0, _actions2.deregisterMethod)(method));
  },
  onAddAvailableMethod: method => {
    dispatch((0, _actions.addAvailableMethod)(method));
  },
  onSetDefaultMethod: urlSegment => dispatch((0, _actions2.setDefaultMethod)(urlSegment))
}))(Remove);

/***/ }),

/***/ "./client/src/components/FormField/RegisteredMFAMethodListField/MethodListItem/Reset.js":
/*!**********************************************************************************************!*\
  !*** ./client/src/components/FormField/RegisteredMFAMethodListField/MethodListItem/Reset.js ***!
  \**********************************************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _react = _interopRequireWildcard(__webpack_require__(/*! react */ "react"));
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
var _reactRedux = __webpack_require__(/*! react-redux */ "react-redux");
var _Register = __webpack_require__(/*! components/Register */ "./client/src/components/Register.js");
var _actions = __webpack_require__(/*! state/mfaRegister/actions */ "./client/src/state/mfaRegister/actions.js");
var _registeredMethod = _interopRequireDefault(__webpack_require__(/*! types/registeredMethod */ "./client/src/types/registeredMethod.js"));
var _RegisterModal = _interopRequireDefault(__webpack_require__(/*! components/RegisterModal */ "./client/src/components/RegisterModal.js"));
var _en = _interopRequireDefault(__webpack_require__(/*! ../../../../../lang/src/en.json */ "./client/lang/src/en.json"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
function _getRequireWildcardCache(e) { if ("function" != typeof WeakMap) return null; var r = new WeakMap(), t = new WeakMap(); return (_getRequireWildcardCache = function (e) { return e ? t : r; })(e); }
function _interopRequireWildcard(e, r) { if (!r && e && e.__esModule) return e; if (null === e || "object" != typeof e && "function" != typeof e) return { default: e }; var t = _getRequireWildcardCache(r); if (t && t.has(e)) return t.get(e); var n = { __proto__: null }, a = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var u in e) if ("default" !== u && {}.hasOwnProperty.call(e, u)) { var i = a ? Object.getOwnPropertyDescriptor(e, u) : null; i && (i.get || i.set) ? Object.defineProperty(n, u, i) : n[u] = e[u]; } return n.default = e, t && t.set(e, n), n; }
function Reset(props) {
  const {
    allAvailableMethods,
    backupMethod,
    endpoints,
    onReset,
    onResetMethod,
    method,
    resources
  } = props;
  const [modalOpen, setModalOpen] = (0, _react.useState)(false);
  function handleToggleModal() {
    setModalOpen(!modalOpen);
  }
  function handleReset() {
    const availableMethodDetail = allAvailableMethods.find(candidate => candidate.urlSegment === method.urlSegment);
    if (!availableMethodDetail) {
      throw Error(`Cannot register the method given: ${method.name} (${method.urlSegment}).`);
    }
    onResetMethod(availableMethodDetail);
    handleToggleModal();
  }
  const callback = onReset ? () => onReset(handleReset) : handleReset;
  return _react.default.createElement("button", {
    className: "registered-method-list-item__control",
    type: "button",
    onClick: callback
  }, window.ss.i18n._t('MultiFactorAuthentication.RESET_METHOD', _en.default['MultiFactorAuthentication.RESET_METHOD']), _react.default.createElement(_RegisterModal.default, {
    backupMethod: backupMethod,
    isOpen: modalOpen,
    toggle: () => handleToggleModal(),
    resources: resources,
    endpoints: endpoints,
    disallowedScreens: [_Register.SCREEN_CHOOSE_METHOD, _Register.SCREEN_INTRODUCTION]
  }));
}
Reset.propTypes = {
  method: _registeredMethod.default.isRequired,
  onReset: _propTypes.default.func
};
var _default = exports["default"] = (0, _reactRedux.connect)(null, dispatch => ({
  onResetMethod: method => {
    dispatch((0, _actions.chooseMethod)(method));
    dispatch((0, _actions.showScreen)(_Register.SCREEN_REGISTER_METHOD));
  }
}))(Reset);

/***/ }),

/***/ "./client/src/components/FormField/RegisteredMFAMethodListField/MethodListItem/SetDefault.js":
/*!***************************************************************************************************!*\
  !*** ./client/src/components/FormField/RegisteredMFAMethodListField/MethodListItem/SetDefault.js ***!
  \***************************************************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports.Component = SetDefault;
exports["default"] = void 0;
var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
var _registeredMethod = _interopRequireDefault(__webpack_require__(/*! types/registeredMethod */ "./client/src/types/registeredMethod.js"));
var _Config = _interopRequireDefault(__webpack_require__(/*! lib/Config */ "lib/Config"));
var _reactRedux = __webpack_require__(/*! react-redux */ "react-redux");
var _api = _interopRequireDefault(__webpack_require__(/*! lib/api */ "./client/src/lib/api.js"));
var _actions = __webpack_require__(/*! state/mfaAdministration/actions */ "./client/src/state/mfaAdministration/actions.js");
var _en = _interopRequireDefault(__webpack_require__(/*! ../../../../../lang/src/en.json */ "./client/lang/src/en.json"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
function SetDefault(props) {
  const {
    endpoints,
    method,
    onSetDefaultMethod
  } = props;
  const i18n = window.ss.i18n;
  function handleSetDefault() {
    const {
      setDefault
    } = endpoints;
    const token = _Config.default.get('SecurityID');
    const endpoint = `${setDefault.replace('{urlSegment}', method.urlSegment)}?SecurityID=${token}`;
    (0, _api.default)(endpoint, 'PUT').then(response => response.json().then(json => {
      if (response.status === 200) {
        onSetDefaultMethod(method.urlSegment);
        return;
      }
      const message = json.errors && ` Errors: \n - ${json.errors.join('\n -')}` || '';
      throw Error(`Could not set default method. Error code ${response.status}.${message}`);
    }));
  }
  return _react.default.createElement("button", {
    className: "registered-method-list-item__control",
    type: "button",
    onClick: handleSetDefault
  }, i18n._t('MultiFactorAuthentication.SET_AS_DEFAULT', _en.default['MultiFactorAuthentication.SET_AS_DEFAULT']));
}
SetDefault.propTypes = {
  method: _registeredMethod.default.isRequired
};
SetDefault.contextTypes = {
  endpoints: _propTypes.default.shape({
    setDefault: _propTypes.default.string
  })
};
const mapDispatchToProps = dispatch => ({
  onSetDefaultMethod: urlSegment => dispatch((0, _actions.setDefaultMethod)(urlSegment))
});
var _default = exports["default"] = (0, _reactRedux.connect)(null, mapDispatchToProps)(SetDefault);

/***/ }),

/***/ "./client/src/components/FormField/RegisteredMFAMethodListField/RegisteredMFAMethodListField.js":
/*!******************************************************************************************************!*\
  !*** ./client/src/components/FormField/RegisteredMFAMethodListField/RegisteredMFAMethodListField.js ***!
  \******************************************************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports.Component = RegisteredMFAMethodListField;
exports["default"] = void 0;
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
var _react = _interopRequireWildcard(__webpack_require__(/*! react */ "react"));
var _reactstrap = __webpack_require__(/*! reactstrap */ "reactstrap");
var _reactRedux = __webpack_require__(/*! react-redux */ "react-redux");
var _classnames = _interopRequireDefault(__webpack_require__(/*! classnames */ "classnames"));
var _registeredMethod = _interopRequireDefault(__webpack_require__(/*! types/registeredMethod */ "./client/src/types/registeredMethod.js"));
var _availableMethod = _interopRequireDefault(__webpack_require__(/*! types/availableMethod */ "./client/src/types/availableMethod.js"));
var _actions = __webpack_require__(/*! state/mfaRegister/actions */ "./client/src/state/mfaRegister/actions.js");
var _actions2 = __webpack_require__(/*! state/mfaAdministration/actions */ "./client/src/state/mfaAdministration/actions.js");
var _Register = __webpack_require__(/*! components/Register */ "./client/src/components/Register.js");
var _MethodListItem = _interopRequireDefault(__webpack_require__(/*! ./MethodListItem */ "./client/src/components/FormField/RegisteredMFAMethodListField/MethodListItem.js"));
var _AccountResetUI = _interopRequireDefault(__webpack_require__(/*! ./AccountResetUI */ "./client/src/components/FormField/RegisteredMFAMethodListField/AccountResetUI.js"));
var _RegisterModal = _interopRequireDefault(__webpack_require__(/*! ../../RegisterModal */ "./client/src/components/RegisterModal.js"));
var _en = _interopRequireDefault(__webpack_require__(/*! ../../../../lang/src/en.json */ "./client/lang/src/en.json"));
function _getRequireWildcardCache(e) { if ("function" != typeof WeakMap) return null; var r = new WeakMap(), t = new WeakMap(); return (_getRequireWildcardCache = function (e) { return e ? t : r; })(e); }
function _interopRequireWildcard(e, r) { if (!r && e && e.__esModule) return e; if (null === e || "object" != typeof e && "function" != typeof e) return { default: e }; var t = _getRequireWildcardCache(r); if (t && t.has(e)) return t.get(e); var n = { __proto__: null }, a = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var u in e) if ("default" !== u && {}.hasOwnProperty.call(e, u)) { var i = a ? Object.getOwnPropertyDescriptor(e, u) : null; i && (i.get || i.set) ? Object.defineProperty(n, u, i) : n[u] = e[u]; } return n.default = e, t && t.set(e, n), n; }
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
function RegisteredMFAMethodListField(props) {
  const {
    allAvailableMethods,
    availableMethods,
    backupCreatedDate,
    backupMethod,
    defaultMethod,
    endpoints,
    initialAvailableMethods,
    initialDefaultMethod,
    initialRegisteredMethods,
    isMFARequired,
    MethodListItemComponent,
    onResetRegister,
    onSetDefaultMethod,
    onSetRegisteredMethods,
    onUpdateAvailableMethods,
    readOnly,
    registeredMethods,
    RegisterModalComponent,
    resetEndpoint,
    resources
  } = props;
  const [modalOpen, setModalOpen] = (0, _react.useState)(false);
  const i18n = window.ss.i18n;
  (0, _react.useEffect)(() => {
    onSetRegisteredMethods(initialRegisteredMethods);
    onUpdateAvailableMethods(initialAvailableMethods);
    onSetDefaultMethod(initialDefaultMethod);
  }, []);
  function getBaseMethods() {
    let {
      methods
    } = registeredMethods;
    if (!methods) {
      return [];
    }
    if (backupMethod) {
      methods = methods.filter(method => method.urlSegment !== backupMethod.urlSegment);
    }
    return methods;
  }
  function handleToggleModal() {
    setModalOpen(!modalOpen);
  }
  function renderNoMethodsMessage() {
    if (getBaseMethods().length) {
      return null;
    }
    const messageKey = readOnly ? 'MultiFactorAuthentication.NO_METHODS_REGISTERED_READONLY' : 'MultiFactorAuthentication.NO_METHODS_REGISTERED';
    return _react.default.createElement("div", {
      className: "registered-mfa-method-list-field__no-methods"
    }, i18n._t(messageKey, _en.default[messageKey]));
  }
  function renderBackupMethod() {
    if (!backupMethod) {
      return null;
    }
    const registeredBackupMethod = registeredMethods.find(candidate => candidate.urlSegment === backupMethod.urlSegment);
    if (!registeredBackupMethod) {
      return null;
    }
    return _react.default.createElement(MethodListItemComponent, {
      method: registeredBackupMethod,
      createdDate: backupCreatedDate,
      canReset: !readOnly,
      isBackupMethod: true,
      tag: "div",
      className: "registered-method-list-item--backup"
    });
  }
  function renderBaseMethods() {
    const baseMethods = getBaseMethods();
    if (!baseMethods.length) {
      return [];
    }
    return baseMethods.map(method => {
      const newProps = {
        allAvailableMethods,
        backupMethod,
        endpoints,
        method,
        resources,
        key: method.urlSegment,
        isDefaultMethod: defaultMethod && method.urlSegment === defaultMethod,
        canRemove: !readOnly && !(isMFARequired && baseMethods.length === 1),
        canReset: !readOnly
      };
      return _react.default.createElement(MethodListItemComponent, newProps);
    });
  }
  function renderModal() {
    return _react.default.createElement(RegisterModalComponent, {
      backupMethod: backupMethod,
      isOpen: modalOpen,
      toggle: () => handleToggleModal(),
      resources: resources,
      endpoints: endpoints,
      disallowedScreens: [_Register.SCREEN_INTRODUCTION]
    });
  }
  function renderAddButton() {
    if (readOnly || !availableMethods || availableMethods.length === 0) {
      return null;
    }
    const label = registeredMethods.length ? i18n._t('MultiFactorAuthentication.ADD_ANOTHER_METHOD', _en.default['MultiFactorAuthentication.ADD_ANOTHER_METHOD']) : i18n._t('MultiFactorAuthentication.ADD_FIRST_METHOD', _en.default['MultiFactorAuthentication.ADD_FIRST_METHOD']);
    return _react.default.createElement(_reactstrap.Button, {
      className: "registered-mfa-method-list-field__button",
      outline: true,
      type: "button",
      onClick: () => {
        handleToggleModal();
        onResetRegister();
      }
    }, label);
  }
  const classNames = (0, _classnames.default)({
    'registered-mfa-method-list-field': true,
    'registered-mfa-method-list-field--read-only': readOnly
  });
  return _react.default.createElement("div", {
    className: classNames
  }, _react.default.createElement("ul", {
    className: "method-list"
  }, renderBaseMethods()), renderNoMethodsMessage(), renderAddButton(), renderBackupMethod(), readOnly && _react.default.createElement("hr", null), readOnly && _react.default.createElement(_AccountResetUI.default, {
    resetEndpoint: resetEndpoint
  }), renderModal());
}
RegisteredMFAMethodListField.propTypes = {
  backupMethod: _registeredMethod.default,
  defaultMethod: _propTypes.default.string,
  readOnly: _propTypes.default.bool,
  isMFARequired: _propTypes.default.bool,
  initialDefaultMethod: _propTypes.default.string,
  initialRegisteredMethods: _propTypes.default.arrayOf(_registeredMethod.default),
  initialAvailableMethods: _propTypes.default.arrayOf(_availableMethod.default),
  allAvailableMethods: _propTypes.default.arrayOf(_availableMethod.default),
  resetEndpoint: _propTypes.default.string,
  endpoints: _propTypes.default.shape({
    register: _propTypes.default.string,
    remove: _propTypes.default.string
  }),
  resources: _propTypes.default.object,
  availableMethods: _propTypes.default.arrayOf(_availableMethod.default),
  registeredMethods: _propTypes.default.arrayOf(_registeredMethod.default),
  registrationScreen: _propTypes.default.number,
  MethodListItemComponent: _propTypes.default.oneOfType([_propTypes.default.object, _propTypes.default.func]),
  RegisterModalComponent: _propTypes.default.oneOfType([_propTypes.default.object, _propTypes.default.func])
};
RegisteredMFAMethodListField.defaultProps = {
  initialAvailableMethods: [],
  MethodListItemComponent: _MethodListItem.default,
  RegisterModalComponent: _RegisterModal.default
};
RegisteredMFAMethodListField.childContextTypes = {
  allAvailableMethods: _propTypes.default.arrayOf(_availableMethod.default),
  backupMethod: _registeredMethod.default,
  endpoints: _propTypes.default.shape({
    register: _propTypes.default.string,
    remove: _propTypes.default.string,
    setDefault: _propTypes.default.string
  }),
  resources: _propTypes.default.object
};
const mapDispatchToProps = dispatch => ({
  onResetRegister: () => {
    dispatch((0, _actions.chooseMethod)(null));
    dispatch((0, _actions.showScreen)(_Register.SCREEN_CHOOSE_METHOD));
  },
  onUpdateAvailableMethods: methods => {
    dispatch((0, _actions.setAvailableMethods)(methods));
  },
  onSetDefaultMethod: urlSegment => {
    dispatch((0, _actions2.setDefaultMethod)(urlSegment));
  },
  onSetRegisteredMethods: methods => {
    dispatch((0, _actions2.setRegisteredMethods)(methods));
  }
});
const mapStateToProps = state => {
  const {
    availableMethods,
    screen
  } = state.mfaRegister;
  const {
    defaultMethod,
    registeredMethods
  } = state.mfaAdministration;
  return {
    availableMethods,
    defaultMethod,
    registeredMethods: registeredMethods || [],
    registrationScreen: screen
  };
};
var _default = exports["default"] = (0, _reactRedux.connect)(mapStateToProps, mapDispatchToProps)(RegisteredMFAMethodListField);

/***/ }),

/***/ "./client/src/components/Icons/CircleDash.js":
/*!***************************************************!*\
  !*** ./client/src/components/Icons/CircleDash.js ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
var _default = _ref => {
  let {
    color = 'currentColor',
    size = '3em'
  } = _ref;
  return _react.default.createElement("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    viewBox: "0 0 512 512",
    height: size,
    width: size
  }, _react.default.createElement("g", {
    fill: color
  }, _react.default.createElement("path", {
    d: "M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zM124 296c-6.6 0-12-5.4-12-12v-56c0-6.6 5.4-12 12-12h264c6.6 0 12 5.4 12 12v56c0 6.6-5.4 12-12 12H124z"
  })));
};
exports["default"] = _default;

/***/ }),

/***/ "./client/src/components/Icons/CircleTick.js":
/*!***************************************************!*\
  !*** ./client/src/components/Icons/CircleTick.js ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
var _default = _ref => {
  let {
    color = 'currentColor',
    size = '3em'
  } = _ref;
  return _react.default.createElement("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    viewBox: "0 0 512 512",
    height: size,
    width: size
  }, _react.default.createElement("g", {
    fill: color
  }, _react.default.createElement("path", {
    d: "M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z"
  })));
};
exports["default"] = _default;

/***/ }),

/***/ "./client/src/components/LoadingIndicator.js":
/*!***************************************************!*\
  !*** ./client/src/components/LoadingIndicator.js ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));
var _classnames = _interopRequireDefault(__webpack_require__(/*! classnames */ "classnames"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
var _default = _ref => {
  let {
    block = false,
    size = '6em'
  } = _ref;
  return _react.default.createElement("div", {
    style: {
      height: size,
      width: size
    },
    className: (0, _classnames.default)({
      'mfa-loading-indicator': true,
      'mfa-loading-indicator--block': block
    })
  });
};
exports["default"] = _default;

/***/ }),

/***/ "./client/src/components/Register.js":
/*!*******************************************!*\
  !*** ./client/src/components/Register.js ***!
  \*******************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports.Component = Register;
exports["default"] = exports.SCREEN_REGISTER_METHOD = exports.SCREEN_INTRODUCTION = exports.SCREEN_COMPLETE = exports.SCREEN_CHOOSE_METHOD = void 0;
var _react = _interopRequireWildcard(__webpack_require__(/*! react */ "react"));
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
var _api = _interopRequireDefault(__webpack_require__(/*! lib/api */ "./client/src/lib/api.js"));
var _Injector = __webpack_require__(/*! lib/Injector */ "lib/Injector");
var _availableMethod = _interopRequireDefault(__webpack_require__(/*! types/availableMethod */ "./client/src/types/availableMethod.js"));
var _registeredMethod = _interopRequireDefault(__webpack_require__(/*! types/registeredMethod */ "./client/src/types/registeredMethod.js"));
var _LoadingIndicator = _interopRequireDefault(__webpack_require__(/*! components/LoadingIndicator */ "./client/src/components/LoadingIndicator.js"));
var _Introduction = _interopRequireDefault(__webpack_require__(/*! components/Register/Introduction */ "./client/src/components/Register/Introduction.js"));
var _Complete = _interopRequireDefault(__webpack_require__(/*! components/Register/Complete */ "./client/src/components/Register/Complete.js"));
var _SelectMethod = _interopRequireDefault(__webpack_require__(/*! components/Register/SelectMethod */ "./client/src/components/Register/SelectMethod.js"));
var _reactRedux = __webpack_require__(/*! react-redux */ "react-redux");
var _actions = __webpack_require__(/*! state/mfaRegister/actions */ "./client/src/state/mfaRegister/actions.js");
var _Title = _interopRequireDefault(__webpack_require__(/*! components/Register/Title */ "./client/src/components/Register/Title.js"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
function _getRequireWildcardCache(e) { if ("function" != typeof WeakMap) return null; var r = new WeakMap(), t = new WeakMap(); return (_getRequireWildcardCache = function (e) { return e ? t : r; })(e); }
function _interopRequireWildcard(e, r) { if (!r && e && e.__esModule) return e; if (null === e || "object" != typeof e && "function" != typeof e) return { default: e }; var t = _getRequireWildcardCache(r); if (t && t.has(e)) return t.get(e); var n = { __proto__: null }, a = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var u in e) if ("default" !== u && {}.hasOwnProperty.call(e, u)) { var i = a ? Object.getOwnPropertyDescriptor(e, u) : null; i && (i.get || i.set) ? Object.defineProperty(n, u, i) : n[u] = e[u]; } return n.default = e, t && t.set(e, n), n; }
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const SCREEN_INTRODUCTION = exports.SCREEN_INTRODUCTION = 1;
const SCREEN_REGISTER_METHOD = exports.SCREEN_REGISTER_METHOD = 2;
const SCREEN_CHOOSE_METHOD = exports.SCREEN_CHOOSE_METHOD = 3;
const SCREEN_COMPLETE = exports.SCREEN_COMPLETE = 4;
function Register(props) {
  const {
    availableMethods,
    backupMethod,
    canSkip,
    completeMessage,
    CompleteComponent,
    endpoints,
    IntroductionComponent,
    onCompleteRegistration,
    onRegister,
    onRemoveAvailableMethod,
    onShowComplete,
    onSelectMethod,
    onShowIntroduction,
    onShowChooseMethod,
    registeredMethods,
    resources,
    screen,
    selectedMethod,
    SelectMethodComponent,
    showSubTitle,
    showTitle,
    TitleComponent
  } = props;
  const [registerProps, setRegisterProps] = (0, _react.useState)(null);
  const [token, setToken] = (0, _react.useState)(null);
  const i18n = window.ss.i18n;
  function fetchStartRegistrationData() {
    const {
      register
    } = endpoints;
    const endpoint = register.replace('{urlSegment}', selectedMethod.urlSegment);
    (0, _api.default)(endpoint).then(response => response.json().then(result => {
      const {
        SecurityID,
        ...other
      } = result;
      setRegisterProps(other);
      setToken(SecurityID);
    }));
  }
  (0, _react.useEffect)(() => {
    if (selectedMethod) {
      fetchStartRegistrationData();
    }
  }, [selectedMethod]);
  function shouldSetupBackupMethod() {
    if (!backupMethod) {
      return false;
    }
    return !registeredMethods.find(method => method.urlSegment === backupMethod.urlSegment);
  }
  function setupBackupMethod() {
    if (shouldSetupBackupMethod() && selectedMethod.urlSegment !== backupMethod.urlSegment) {
      onSelectMethod(backupMethod);
      return;
    }
    onShowComplete();
  }
  function handleBack() {
    if (availableMethods.length === 1 && onShowIntroduction) {
      return onShowIntroduction();
    }
    setRegisterProps(null);
    return onShowChooseMethod();
  }
  function handleCompleteRegistration(registrationData) {
    const {
      register
    } = endpoints;
    const params = token ? `?SecurityID=${token}` : '';
    (0, _api.default)(`${register.replace('{urlSegment}', selectedMethod.urlSegment)}${params}`, 'POST', JSON.stringify(registrationData)).then(response => {
      switch (response.status) {
        case 201:
          setRegisterProps(null);
          if (typeof onRegister === 'function') {
            onRegister(selectedMethod);
          }
          onRemoveAvailableMethod(selectedMethod);
          setupBackupMethod();
          return null;
        default:
      }
      return response.json();
    }).then(response => {
      if (response && response.errors) {
        const formattedErrors = response.errors.join(', ');
        setRegisterProps({
          ...registerProps,
          error: formattedErrors
        });
      }
    });
  }
  function handleSkip() {
    const {
      skip
    } = endpoints;
    if (skip) {
      window.location = skip;
    }
  }
  function renderIntroduction() {
    const {
      skip
    } = endpoints;
    return _react.default.createElement(IntroductionComponent, {
      canSkip: skip && canSkip,
      onSkip: () => handleSkip(),
      resources: () => resources(),
      showTitle: () => showSubTitle()
    });
  }
  function renderMethod() {
    if (!selectedMethod) {
      return null;
    }
    if (!registerProps) {
      return _react.default.createElement(_LoadingIndicator.default, {
        block: true
      });
    }
    const RegistrationComponent = (0, _Injector.loadComponent)(selectedMethod.component);
    return _react.default.createElement("div", null, showSubTitle && _react.default.createElement(TitleComponent, null), _react.default.createElement(RegistrationComponent, _extends({}, registerProps, {
      method: selectedMethod,
      onBack: () => handleBack(),
      onCompleteRegistration: () => handleCompleteRegistration()
    })));
  }
  function renderOptions() {
    return _react.default.createElement(SelectMethodComponent, {
      methods: availableMethods,
      showTitle: showSubTitle
    });
  }
  if (screen === SCREEN_COMPLETE) {
    return _react.default.createElement(CompleteComponent, {
      showTitle: showSubTitle,
      onComplete: onCompleteRegistration,
      message: completeMessage
    });
  }
  let content;
  switch (screen) {
    case SCREEN_CHOOSE_METHOD:
      content = renderOptions();
      break;
    case SCREEN_REGISTER_METHOD:
      content = renderMethod();
      break;
    case SCREEN_INTRODUCTION:
    default:
      content = renderIntroduction();
      break;
  }
  return _react.default.createElement("div", null, showTitle && _react.default.createElement("h1", {
    className: "mfa-app-title"
  }, i18n._t('MFARegister.TITLE', 'Multi-factor authentication')), content);
}
Register.propTypes = {
  availableMethods: _propTypes.default.arrayOf(_availableMethod.default),
  backupMethod: _availableMethod.default,
  canSkip: _propTypes.default.bool,
  endpoints: _propTypes.default.shape({
    register: _propTypes.default.string.isRequired,
    skip: _propTypes.default.string
  }),
  onRegister: _propTypes.default.func,
  onCompleteRegistration: _propTypes.default.func.isRequired,
  registeredMethods: _propTypes.default.arrayOf(_registeredMethod.default),
  resources: _propTypes.default.object,
  showTitle: _propTypes.default.bool,
  showSubTitle: _propTypes.default.bool,
  IntroductionComponent: _propTypes.default.oneOfType([_propTypes.default.object, _propTypes.default.func]),
  SelectMethodComponent: _propTypes.default.oneOfType([_propTypes.default.object, _propTypes.default.func]),
  CompleteComponent: _propTypes.default.oneOfType([_propTypes.default.object, _propTypes.default.func]),
  TitleComponent: _propTypes.default.oneOfType([_propTypes.default.object, _propTypes.default.func])
};
Register.defaultProps = {
  resources: {},
  showTitle: true,
  showSubTitle: true,
  showIntroduction: true,
  IntroductionComponent: _Introduction.default,
  SelectMethodComponent: _SelectMethod.default,
  CompleteComponent: _Complete.default,
  TitleComponent: _Title.default
};
const mapStateToProps = state => {
  const source = state.mfaRegister || state;
  return {
    screen: source.screen,
    selectedMethod: source.method,
    availableMethods: source.availableMethods
  };
};
const mapDispatchToProps = dispatch => ({
  onShowIntroduction: () => dispatch((0, _actions.showScreen)(SCREEN_INTRODUCTION)),
  onShowComplete: () => dispatch((0, _actions.showScreen)(SCREEN_COMPLETE)),
  onSelectMethod: method => dispatch((0, _actions.chooseMethod)(method)),
  onShowChooseMethod: () => {
    dispatch((0, _actions.chooseMethod)(null));
    dispatch((0, _actions.showScreen)(SCREEN_CHOOSE_METHOD));
  },
  onRemoveAvailableMethod: method => dispatch((0, _actions.removeAvailableMethod)(method))
});
var _default = exports["default"] = (0, _reactRedux.connect)(mapStateToProps, mapDispatchToProps)(Register);

/***/ }),

/***/ "./client/src/components/Register/Complete.js":
/*!****************************************************!*\
  !*** ./client/src/components/Register/Complete.js ***!
  \****************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
var _Title = _interopRequireDefault(__webpack_require__(/*! ./Title */ "./client/src/components/Register/Title.js"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
const Complete = _ref => {
  let {
    onComplete,
    showTitle,
    message
  } = _ref;
  return _react.default.createElement("div", {
    className: "mfa-register-confirmation"
  }, _react.default.createElement("i", {
    className: "font-icon-check-mark mfa-register-confirmation__icon"
  }), showTitle && _react.default.createElement(_Title.default, {
    className: "mfa-register-confirmation__title"
  }), _react.default.createElement("p", {
    className: "mfa-register-confirmation__description"
  }, message || window.ss.i18n._t('MFARegister.SETUP_COMPLETE_DESCRIPTION', 'You will be able to edit these settings later from your profile area.')), _react.default.createElement("button", {
    onClick: onComplete,
    className: "mfa-register-confirmation__continue btn btn-primary"
  }, window.ss.i18n._t('MFARegister.SETUP_COMPLETE_CONTINUE', 'Continue')));
};
Complete.propTypes = {
  onComplete: _propTypes.default.func.isRequired,
  showTitle: _propTypes.default.bool
};
Complete.defaultProps = {
  showTitle: true
};
var _default = exports["default"] = Complete;

/***/ }),

/***/ "./client/src/components/Register/Introduction.js":
/*!********************************************************!*\
  !*** ./client/src/components/Register/Introduction.js ***!
  \********************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = exports.Component = exports.ActionList = void 0;
var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
var _actions = __webpack_require__(/*! state/mfaRegister/actions */ "./client/src/state/mfaRegister/actions.js");
var _reactRedux = __webpack_require__(/*! react-redux */ "react-redux");
var _Register = __webpack_require__(/*! ../Register */ "./client/src/components/Register.js");
var _Title = _interopRequireDefault(__webpack_require__(/*! ./Title */ "./client/src/components/Register/Title.js"));
var _en = _interopRequireDefault(__webpack_require__(/*! ../../../lang/src/en.json */ "./client/lang/src/en.json"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
const ActionList = _ref => {
  let {
    canSkip,
    onContinue,
    onSkip
  } = _ref;
  const {
    ss: {
      i18n
    }
  } = window;
  return _react.default.createElement("ul", {
    className: "mfa-action-list"
  }, _react.default.createElement("li", {
    className: "mfa-action-list__item"
  }, _react.default.createElement("button", {
    className: "btn btn-primary",
    onClick: onContinue
  }, i18n._t('MultiFactorAuthentication.GET_STARTED', _en.default['MultiFactorAuthentication.GET_STARTED']))), canSkip && _react.default.createElement("li", {
    className: "mfa-action-list__item"
  }, _react.default.createElement("button", {
    className: "btn btn-secondary",
    onClick: onSkip
  }, i18n._t('MultiFactorAuthentication.SETUP_LATER', _en.default['MultiFactorAuthentication.SETUP_LATER']))));
};
exports.ActionList = ActionList;
const Introduction = _ref2 => {
  let {
    canSkip,
    onContinue,
    onSkip,
    resources,
    showTitle,
    TitleComponent
  } = _ref2;
  const {
    ss: {
      i18n
    }
  } = window;
  return _react.default.createElement("div", null, showTitle && _react.default.createElement(TitleComponent, null), _react.default.createElement("h4", {
    className: "mfa-feature-list-title"
  }, i18n._t('MultiFactorAuthentication.HOW_IT_WORKS', _en.default['MultiFactorAuthentication.HOW_IT_WORKS'])), _react.default.createElement("ul", {
    className: "mfa-feature-list"
  }, _react.default.createElement("li", {
    className: "mfa-feature-list-item"
  }, resources.extra_factor_image_url && _react.default.createElement("img", {
    alt: i18n._t('MultiFactorAuthentication.EXTRA_LAYER_IMAGE_ALT', _en.default['MultiFactorAuthentication.EXTRA_LAYER_IMAGE_ALT']),
    "aria-hidden": "true",
    className: "mfa-feature-list-item__icon",
    src: resources.extra_factor_image_url
  }), _react.default.createElement("div", {
    className: "mfa-feature-list-item__content"
  }, _react.default.createElement("h5", {
    className: "mfa-block-heading mfa-feature-list-item__title"
  }, i18n._t('MultiFactorAuthentication.EXTRA_LAYER_TITLE', _en.default['MultiFactorAuthentication.EXTRA_LAYER_TITLE'])), _react.default.createElement("p", {
    className: "mfa-feature-list-item__description"
  }, i18n._t('MultiFactorAuthentication.EXTRA_LAYER_DESCRIPTION', _en.default['MultiFactorAuthentication.EXTRA_LAYER_DESCRIPTION']), "\xA0", resources.user_help_link && _react.default.createElement("a", {
    href: resources.user_help_link
  }, i18n._t('MultiFactorAuthentication.HOW_MFA_WORKS', _en.default['MultiFactorAuthentication.HOW_MFA_WORKS']))))), _react.default.createElement("li", {
    className: "mfa-feature-list-item"
  }, resources.unique_image_url && _react.default.createElement("img", {
    alt: i18n._t('MultiFactorAuthentication.UNIQUE_IMAGE_ALT', _en.default['MultiFactorAuthentication.UNIQUE_IMAGE_ALT']),
    "aria-hidden": "true",
    className: "mfa-feature-list-item__icon",
    src: resources.unique_image_url
  }), _react.default.createElement("div", {
    className: "mfa-feature-list-item__content"
  }, _react.default.createElement("h5", {
    className: "mfa-block-heading mfa-feature-list-item__title"
  }, i18n._t('MultiFactorAuthentication.UNIQUE_TITLE', _en.default['MultiFactorAuthentication.UNIQUE_TITLE'])), _react.default.createElement("p", {
    className: "mfa-feature-list-item__description"
  }, i18n._t('MultiFactorAuthentication.UNIQUE_DESCRIPTION', _en.default['MultiFactorAuthentication.UNIQUE_DESCRIPTION']))))), _react.default.createElement(ActionList, {
    canSkip: canSkip,
    onContinue: onContinue,
    onSkip: onSkip
  }));
};
exports.Component = Introduction;
Introduction.propTypes = {
  canSkip: _propTypes.default.bool,
  onContinue: _propTypes.default.func.isRequired,
  onSkip: _propTypes.default.func,
  resources: _propTypes.default.shape({
    user_help_link: _propTypes.default.string,
    extra_factor_image_url: _propTypes.default.string,
    unique_image_url: _propTypes.default.string
  }).isRequired,
  showTitle: _propTypes.default.bool,
  TitleComponent: _propTypes.default.oneOfType([_propTypes.default.object, _propTypes.default.func])
};
Introduction.defaultProps = {
  showTitle: true,
  TitleComponent: _Title.default
};
const mapDispatchToProps = dispatch => ({
  onContinue: () => {
    dispatch((0, _actions.chooseMethod)(null));
    dispatch((0, _actions.showScreen)(_Register.SCREEN_REGISTER_METHOD));
  }
});
var _default = exports["default"] = (0, _reactRedux.connect)(null, mapDispatchToProps)(Introduction);

/***/ }),

/***/ "./client/src/components/Register/MethodTile.js":
/*!******************************************************!*\
  !*** ./client/src/components/Register/MethodTile.js ***!
  \******************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports.Component = MethodTile;
exports["default"] = void 0;
var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
var _classnames = _interopRequireDefault(__webpack_require__(/*! classnames */ "classnames"));
var _availableMethod = _interopRequireDefault(__webpack_require__(/*! types/availableMethod */ "./client/src/types/availableMethod.js"));
var _withMethodAvailability = _interopRequireDefault(__webpack_require__(/*! state/methodAvailability/withMethodAvailability */ "./client/src/state/methodAvailability/withMethodAvailability.js"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
function MethodTile(props) {
  const {
    isActive,
    isAvailable,
    getUnavailableMessage,
    method,
    onClick
  } = props;
  const i18n = window.ss.i18n;
  function handleClick(event) {
    if (method.isAvailable && onClick) {
      onClick(event);
    }
  }
  function handleKeyUp(event) {
    if (event.keyCode === 13) {
      handleClick(event);
    }
  }
  function renderSupportLink() {
    const {
      supportLink,
      supportText
    } = method;
    if (!supportLink) {
      return null;
    }
    return _react.default.createElement("a", {
      href: supportLink,
      target: "_blank",
      rel: "noopener noreferrer",
      className: "mfa-method-tile__support-link"
    }, supportText || i18n._t('MFARegister.HELP', 'Find out more.'));
  }
  function renderUnavailableMask() {
    if (isAvailable()) {
      return null;
    }
    const message = getUnavailableMessage();
    return _react.default.createElement("div", {
      className: "mfa-method-tile__unavailable-mask"
    }, _react.default.createElement("h3", {
      className: "mfa-method-tile__unavailable-title"
    }, i18n._t('MFAMethodTile.UNAVAILABLE', 'Unsupported: ')), message && _react.default.createElement("p", {
      className: "mfa-method-tile__unavailable-text"
    }, message));
  }
  const classes = (0, _classnames.default)('mfa-method-tile', {
    'mfa-method-tile--active': isActive,
    'mfa-method-tile--unsupported': !method.isAvailable
  });
  const thumbnailClasses = (0, _classnames.default)('mfa-method-tile__thumbnail-container', {
    'mfa-method-tile__thumbnail-container--unsupported': !method.isAvailable
  });
  const leadInLabel = i18n.inject(i18n._t('MFARegister.REGISTER_WITH', 'Register with {method}'), {
    method: method.name.toLowerCase()
  });
  return _react.default.createElement("li", {
    className: classes
  }, _react.default.createElement("div", {
    className: "mfa-method-tile__content",
    onClick: handleClick,
    onKeyUp: handleKeyUp,
    tabIndex: "0",
    role: "button"
  }, method.thumbnail && _react.default.createElement("div", {
    className: thumbnailClasses
  }, _react.default.createElement("img", {
    src: method.thumbnail,
    className: "mfa-method-tile__thumbnail",
    alt: method.name
  })), _react.default.createElement("h3", {
    className: "mfa-method-tile__title"
  }, leadInLabel), _react.default.createElement("p", {
    className: "mfa-method-tile__description"
  }, method.description && `${method.description}. `, renderSupportLink())), renderUnavailableMask());
}
MethodTile.propTypes = {
  getUnavailableMessage: _propTypes.default.func.isRequired,
  isActive: _propTypes.default.bool,
  isAvailable: _propTypes.default.func.isRequired,
  method: _availableMethod.default.isRequired,
  onClick: _propTypes.default.func.isRequired
};
MethodTile.defaultProps = {
  isActive: false
};
var _default = exports["default"] = (0, _withMethodAvailability.default)(MethodTile);

/***/ }),

/***/ "./client/src/components/Register/SelectMethod.js":
/*!********************************************************!*\
  !*** ./client/src/components/Register/SelectMethod.js ***!
  \********************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports.Component = SelectMethod;
exports["default"] = void 0;
var _react = _interopRequireWildcard(__webpack_require__(/*! react */ "react"));
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
var _availableMethod = _interopRequireDefault(__webpack_require__(/*! types/availableMethod */ "./client/src/types/availableMethod.js"));
var _classnames = _interopRequireDefault(__webpack_require__(/*! classnames */ "classnames"));
var _actions = __webpack_require__(/*! state/mfaRegister/actions */ "./client/src/state/mfaRegister/actions.js");
var _redux = __webpack_require__(/*! redux */ "redux");
var _reactRedux = __webpack_require__(/*! react-redux */ "react-redux");
var _withMethodAvailability = _interopRequireDefault(__webpack_require__(/*! state/methodAvailability/withMethodAvailability */ "./client/src/state/methodAvailability/withMethodAvailability.js"));
var _Register = __webpack_require__(/*! ../Register */ "./client/src/components/Register.js");
var _MethodTile = _interopRequireDefault(__webpack_require__(/*! ./MethodTile */ "./client/src/components/Register/MethodTile.js"));
var _Title = _interopRequireDefault(__webpack_require__(/*! ./Title */ "./client/src/components/Register/Title.js"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
function _getRequireWildcardCache(e) { if ("function" != typeof WeakMap) return null; var r = new WeakMap(), t = new WeakMap(); return (_getRequireWildcardCache = function (e) { return e ? t : r; })(e); }
function _interopRequireWildcard(e, r) { if (!r && e && e.__esModule) return e; if (null === e || "object" != typeof e && "function" != typeof e) return { default: e }; var t = _getRequireWildcardCache(r); if (t && t.has(e)) return t.get(e); var n = { __proto__: null }, a = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var u in e) if ("default" !== u && {}.hasOwnProperty.call(e, u)) { var i = a ? Object.getOwnPropertyDescriptor(e, u) : null; i && (i.get || i.set) ? Object.defineProperty(n, u, i) : n[u] = e[u]; } return n.default = e, t && t.set(e, n), n; }
function SelectMethod(props) {
  const {
    methods,
    MethodTileComponent,
    onClickBack,
    onSelectMethod,
    showTitle,
    TitleComponent
  } = props;
  const i18n = window.ss.i18n;
  let initialHighlightedMethod = null;
  if (props.methods.length === 1 && props.isAvailable && props.isAvailable(props.methods[0])) {
    initialHighlightedMethod = props.methods[0];
  }
  const [highlightedMethod, setHighlightedMethod] = (0, _react.useState)(initialHighlightedMethod);
  function handleGoToNext() {
    onSelectMethod(highlightedMethod);
  }
  (0, _react.useEffect)(() => {
    if (initialHighlightedMethod) {
      handleGoToNext();
    }
  }, []);
  function handleClick(method) {
    setHighlightedMethod(method);
  }
  function handleBack() {
    if (onClickBack) {
      onClickBack();
    }
  }
  function renderActions() {
    return _react.default.createElement("ul", {
      className: "mfa-action-list"
    }, _react.default.createElement("li", {
      className: "mfa-action-list__item"
    }, _react.default.createElement("button", {
      className: "btn btn-primary",
      disabled: highlightedMethod === null,
      onClick: handleGoToNext
    }, i18n._t('MFARegister.NEXT', 'Next'))), _react.default.createElement("li", {
      className: "mfa-action-list__item"
    }, _react.default.createElement("button", {
      className: "btn btn-secondary",
      onClick: handleBack
    }, i18n._t('MFARegister.BACK', 'Back'))));
  }
  const classes = (0, _classnames.default)('mfa-method-tile-group', {
    'mfa-method-tile-group--three-columns': methods.length % 3 === 0
  });
  return _react.default.createElement("div", null, showTitle && _react.default.createElement(TitleComponent, null), _react.default.createElement("ul", {
    className: classes
  }, methods.map(method => _react.default.createElement(MethodTileComponent, {
    isActive: highlightedMethod === method,
    key: method.urlSegment,
    method: method,
    onClick: () => handleClick(method)
  }))), renderActions());
}
SelectMethod.propTypes = {
  methods: _propTypes.default.arrayOf(_availableMethod.default),
  onSelectMethod: _propTypes.default.func,
  onClickBack: _propTypes.default.func,
  showTitle: _propTypes.default.bool,
  TitleComponent: _propTypes.default.oneOfType([_propTypes.default.object, _propTypes.default.func]),
  MethodTileComponent: _propTypes.default.oneOfType([_propTypes.default.object, _propTypes.default.func])
};
SelectMethod.defaultProps = {
  showTitle: true,
  TitleComponent: _Title.default,
  MethodTileComponent: _MethodTile.default
};
const mapDispatchToProps = dispatch => ({
  onClickBack: () => dispatch((0, _actions.showScreen)(_Register.SCREEN_INTRODUCTION)),
  onSelectMethod: method => {
    dispatch((0, _actions.chooseMethod)(method));
    dispatch((0, _actions.showScreen)(_Register.SCREEN_REGISTER_METHOD));
  }
});
var _default = exports["default"] = (0, _redux.compose)((0, _reactRedux.connect)(null, mapDispatchToProps), _withMethodAvailability.default)(SelectMethod);

/***/ }),

/***/ "./client/src/components/Register/Title.js":
/*!*************************************************!*\
  !*** ./client/src/components/Register/Title.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = exports.Component = void 0;
var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));
var _reactRedux = __webpack_require__(/*! react-redux */ "react-redux");
var _Register = __webpack_require__(/*! ../Register */ "./client/src/components/Register.js");
var _en = _interopRequireDefault(__webpack_require__(/*! ../../../lang/src/en.json */ "./client/lang/src/en.json"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
const Title = _ref => {
  let {
    screen,
    method,
    Tag = 'h2',
    className = 'mfa-section-title'
  } = _ref;
  const {
    ss: {
      i18n
    }
  } = window;
  let content;
  switch (screen) {
    case _Register.SCREEN_INTRODUCTION:
      content = i18n._t('MultiFactorAuthentication.TITLE', _en.default['MultiFactorAuthentication.TITLE']);
      break;
    case _Register.SCREEN_CHOOSE_METHOD:
      content = i18n._t('MultiFactorAuthentication.SELECT_METHOD', _en.default['MultiFactorAuthentication.SELECT_METHOD']);
      break;
    case _Register.SCREEN_COMPLETE:
      content = i18n._t('MultiFactorAuthentication.SETUP_COMPLETE_TITLE', _en.default['MultiFactorAuthentication.SETUP_COMPLETE_TITLE']);
      break;
    case _Register.SCREEN_REGISTER_METHOD:
      content = method && i18n.inject(i18n._t('MFARegister.REGISTER_WITH', 'Register with {method}'), {
        method: method.name.toLowerCase()
      });
      break;
    default:
      content = false;
  }
  if (!content || !content.length) {
    return null;
  }
  const ParsedTag = Tag || 'span';
  return _react.default.createElement(ParsedTag, {
    className: className
  }, content);
};
exports.Component = Title;
const mapStateToProps = state => {
  const source = state.mfaRegister || state;
  return {
    screen: source.screen,
    method: source.method
  };
};
var _default = exports["default"] = (0, _reactRedux.connect)(mapStateToProps)(Title);

/***/ }),

/***/ "./client/src/components/RegisterModal.js":
/*!************************************************!*\
  !*** ./client/src/components/RegisterModal.js ***!
  \************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports.Component = RegisterModal;
exports["default"] = void 0;
var _react = _interopRequireWildcard(__webpack_require__(/*! react */ "react"));
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
var _reactstrap = __webpack_require__(/*! reactstrap */ "reactstrap");
var _Injector = __webpack_require__(/*! lib/Injector */ "lib/Injector");
var _redux = __webpack_require__(/*! redux */ "redux");
var _reactRedux = __webpack_require__(/*! react-redux */ "react-redux");
var _Title = _interopRequireDefault(__webpack_require__(/*! components/Register/Title */ "./client/src/components/Register/Title.js"));
var _actions = __webpack_require__(/*! state/mfaAdministration/actions */ "./client/src/state/mfaAdministration/actions.js");
var _registeredMethod = _interopRequireDefault(__webpack_require__(/*! types/registeredMethod */ "./client/src/types/registeredMethod.js"));
var _Register = __webpack_require__(/*! components/Register */ "./client/src/components/Register.js");
var _en = _interopRequireDefault(__webpack_require__(/*! ../../lang/src/en.json */ "./client/lang/src/en.json"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
function _getRequireWildcardCache(e) { if ("function" != typeof WeakMap) return null; var r = new WeakMap(), t = new WeakMap(); return (_getRequireWildcardCache = function (e) { return e ? t : r; })(e); }
function _interopRequireWildcard(e, r) { if (!r && e && e.__esModule) return e; if (null === e || "object" != typeof e && "function" != typeof e) return { default: e }; var t = _getRequireWildcardCache(r); if (t && t.has(e)) return t.get(e); var n = { __proto__: null }, a = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var u in e) if ("default" !== u && {}.hasOwnProperty.call(e, u)) { var i = a ? Object.getOwnPropertyDescriptor(e, u) : null; i && (i.get || i.set) ? Object.defineProperty(n, u, i) : n[u] = e[u]; } return n.default = e, t && t.set(e, n), n; }
function RegisterModal(props) {
  const {
    backupMethod,
    disallowedScreens,
    endpoints,
    isOpen,
    onAddRegisteredMethod,
    RegisterComponent,
    registeredMethods,
    resources,
    onSetDefaultMethod,
    registrationScreen,
    toggle
  } = props;
  (0, _react.useEffect)(() => {
    if (!isOpen || !disallowedScreens.length) {
      return;
    }
    if (disallowedScreens.includes(registrationScreen)) {
      toggle();
    }
  }, [disallowedScreens, isOpen, registrationScreen, toggle]);
  function handleRegister(method) {
    if (!registeredMethods.length) {
      onSetDefaultMethod(method.urlSegment);
    }
    onAddRegisteredMethod(method);
  }
  return _react.default.createElement(_reactstrap.Modal, {
    isOpen: isOpen,
    toggle: toggle,
    className: "registered-mfa-method-list-field-register-modal"
  }, _react.default.createElement(_reactstrap.ModalHeader, {
    toggle: toggle
  }, _react.default.createElement(_Title.default, {
    Tag: null
  })), _react.default.createElement(_reactstrap.ModalBody, {
    className: "registered-mfa-method-list-field-register-modal__content"
  }, registrationScreen !== _Register.SCREEN_INTRODUCTION && _react.default.createElement(RegisterComponent, {
    backupMethod: backupMethod,
    registeredMethods: registeredMethods,
    onCompleteRegistration: toggle,
    onRegister: () => handleRegister(),
    resources: resources,
    endpoints: endpoints,
    showTitle: false,
    showSubTitle: false,
    completeMessage: window.ss.i18n._t('MultiFactorAuthentication.ADMIN_SETUP_COMPLETE_CONTINUE', _en.default['MultiFactorAuthentication.ADMIN_SETUP_COMPLETE_CONTINUE'])
  })));
}
RegisterModal.propTypes = {
  isOpen: _propTypes.default.bool,
  toggle: _propTypes.default.func,
  disallowedScreens: _propTypes.default.arrayOf(_propTypes.default.number),
  backupMethod: _registeredMethod.default,
  resources: _propTypes.default.object,
  endpoints: _propTypes.default.shape({
    register: _propTypes.default.string
  }),
  registrationScreen: _propTypes.default.number,
  registeredMethods: _propTypes.default.arrayOf(_registeredMethod.default),
  onAddRegisteredMethod: _propTypes.default.func,
  onSetDefaultMethod: _propTypes.default.func,
  RegisterComponent: _propTypes.default.oneOfType([_propTypes.default.element, _propTypes.default.func, _propTypes.default.elementType])
};
RegisterModal.defaultProps = {
  isOpen: false,
  disallowedScreens: []
};
const mapStateToProps = state => ({
  registrationScreen: state.mfaRegister.screen,
  registeredMethods: state.mfaAdministration.registeredMethods
});
const mapDispatchToProps = dispatch => ({
  onAddRegisteredMethod: method => {
    dispatch((0, _actions.registerMethod)(method));
  },
  onSetDefaultMethod: urlSegment => dispatch((0, _actions.setDefaultMethod)(urlSegment))
});
var _default = exports["default"] = (0, _redux.compose)((0, _Injector.inject)(['MFARegister'], RegisterComponent => ({
  RegisterComponent
}), () => 'MFARegisterModal'), (0, _reactRedux.connect)(mapStateToProps, mapDispatchToProps))(RegisterModal);

/***/ }),

/***/ "./client/src/legacy/RegisteredMFAMethodListFieldEntwine.js":
/*!******************************************************************!*\
  !*** ./client/src/legacy/RegisteredMFAMethodListFieldEntwine.js ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __unused_webpack_exports, __webpack_require__) {

"use strict";


var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));
var _client = __webpack_require__(/*! react-dom/client */ "react-dom/client");
var _Injector = __webpack_require__(/*! lib/Injector */ "lib/Injector");
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
const FIELD_SELECTOR = '.js-injector-boot [data-field-type="registered-mfa-method-list-field"]';
window.jQuery.entwine('ss', $ => {
  $(FIELD_SELECTOR).entwine({
    ReactRoot: null,
    onmatch() {
      const RegisteredMFAMethodListField = (0, _Injector.loadComponent)('RegisteredMFAMethodListField');
      const {
        readOnly,
        schema: {
          backupMethod,
          defaultMethod,
          registeredMethods,
          availableMethods,
          allAvailableMethods,
          resources,
          endpoints,
          backupCreatedDate,
          resetEndpoint,
          isMFARequired
        }
      } = this.data('schema');
      let root = this.getReactRoot();
      if (!root) {
        root = (0, _client.createRoot)(this[0]);
        this.setReactRoot(root);
      }
      root.render(_react.default.createElement(RegisteredMFAMethodListField, {
        backupMethod: backupMethod,
        readOnly: readOnly,
        initialDefaultMethod: defaultMethod,
        initialRegisteredMethods: registeredMethods,
        initialAvailableMethods: availableMethods,
        allAvailableMethods: allAvailableMethods,
        resources: resources,
        endpoints: endpoints,
        backupCreatedDate: backupCreatedDate,
        resetEndpoint: resetEndpoint,
        isMFARequired: isMFARequired
      }));
    },
    onunmatch() {
      const root = this.getReactRoot();
      if (root) {
        root.unmount();
        this.setReactRoot(null);
      }
    }
  });
});

/***/ }),

/***/ "./client/src/legacy/SiteConfig.js":
/*!*****************************************!*\
  !*** ./client/src/legacy/SiteConfig.js ***!
  \*****************************************/
/***/ (function() {

"use strict";


window.jQuery.entwine('ss', $ => {
  $('[name="MFARequired"]').entwine({
    onchange() {
      const isRequired = parseInt(this.val(), 10);
      if (isRequired) {
        $('.mfa-settings__grace-period').removeAttr('disabled');
      } else {
        $('.mfa-settings__grace-period').attr('disabled', 'disabled');
      }
    },
    onmatch() {
      this.onchange();
    }
  });
});

/***/ }),

/***/ "./client/src/legacy/index.js":
/*!************************************!*\
  !*** ./client/src/legacy/index.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __unused_webpack_exports, __webpack_require__) {

"use strict";


__webpack_require__(/*! ./RegisteredMFAMethodListFieldEntwine */ "./client/src/legacy/RegisteredMFAMethodListFieldEntwine.js");
__webpack_require__(/*! ./SiteConfig */ "./client/src/legacy/SiteConfig.js");

/***/ }),

/***/ "./client/src/lib/api.js":
/*!*******************************!*\
  !*** ./client/src/lib/api.js ***!
  \*******************************/
/***/ (function(__unused_webpack_module, exports) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
const api = function (endpoint) {
  let method = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'GET';
  let body = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : undefined;
  let headers = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {};
  return fetch(endpoint, {
    body,
    credentials: 'same-origin',
    headers,
    method
  });
};
var _default = exports["default"] = api;

/***/ }),

/***/ "./client/src/lib/formatCode.js":
/*!**************************************!*\
  !*** ./client/src/lib/formatCode.js ***!
  \**************************************/
/***/ (function(__unused_webpack_module, exports) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports.formatCode = void 0;
const formatCode = function (code) {
  let delimiter = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : ' ';
  if (code.length < 6) {
    return code;
  }
  if (code.length % 4 === 0) {
    return code.split(/(.{4})/g).filter(c => c).join(delimiter).trim();
  }
  if (code.length % 3 === 0) {
    return code.split(/(.{3})/g).filter(c => c).join(delimiter).trim();
  }
  const groupsOfThree = 4 - code.length % 4;
  const groupsOfFour = (code.length - groupsOfThree * 3) / 4;
  const chunkSizes = [...[...Array(groupsOfFour).keys()].map(() => 4), ...[...Array(groupsOfThree).keys()].map(() => 3)];
  let pointer = 0;
  const chunks = chunkSizes.map(size => code.substring(pointer, pointer += size));
  return chunks.join(delimiter).trim();
};
exports.formatCode = formatCode;

/***/ }),

/***/ "./client/src/state/methodAvailability/withMethodAvailability.js":
/*!***********************************************************************!*\
  !*** ./client/src/state/methodAvailability/withMethodAvailability.js ***!
  \***********************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports.hoc = exports["default"] = void 0;
var _react = _interopRequireWildcard(__webpack_require__(/*! react */ "react"));
var _reactRedux = __webpack_require__(/*! react-redux */ "react-redux");
var _redux = __webpack_require__(/*! redux */ "redux");
function _getRequireWildcardCache(e) { if ("function" != typeof WeakMap) return null; var r = new WeakMap(), t = new WeakMap(); return (_getRequireWildcardCache = function (e) { return e ? t : r; })(e); }
function _interopRequireWildcard(e, r) { if (!r && e && e.__esModule) return e; if (null === e || "object" != typeof e && "function" != typeof e) return { default: e }; var t = _getRequireWildcardCache(r); if (t && t.has(e)) return t.get(e); var n = { __proto__: null }, a = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var u in e) if ("default" !== u && {}.hasOwnProperty.call(e, u)) { var i = a ? Object.getOwnPropertyDescriptor(e, u) : null; i && (i.get || i.set) ? Object.defineProperty(n, u, i) : n[u] = e[u]; } return n.default = e, t && t.set(e, n), n; }
function _extends() { return _extends = Object.assign ? Object.assign.bind() : function (n) { for (var e = 1; e < arguments.length; e++) { var t = arguments[e]; for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]); } return n; }, _extends.apply(null, arguments); }
const getDisplayName = WrappedComponent => WrappedComponent.displayName || WrappedComponent.name || 'Component';
const withMethodAvailability = WrappedComponent => {
  const WithMethodAvailability = class extends _react.Component {
    constructor(props) {
      super(props);
      this.getAvailabilityOverride = this.getAvailabilityOverride.bind(this);
      this.isAvailable = this.isAvailable.bind(this);
      this.getUnavailableMessage = this.getUnavailableMessage.bind(this);
    }
    getAvailabilityOverride() {
      let method = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      const {
        availableMethodOverrides
      } = this.props;
      const checkMethod = method || this.props.method;
      const {
        urlSegment
      } = checkMethod;
      if (typeof availableMethodOverrides[urlSegment] !== 'undefined') {
        return availableMethodOverrides[urlSegment];
      }
      return {};
    }
    getUnavailableMessage() {
      let method = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      const checkMethod = method || this.props.method;
      const availabilityOverride = this.getAvailabilityOverride(checkMethod);
      return availabilityOverride.unavailableMessage || checkMethod.unavailableMessage;
    }
    isAvailable() {
      let method = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      const checkMethod = method || this.props.method;
      const availabilityOverride = this.getAvailabilityOverride(checkMethod);
      let isAvailable = checkMethod.isAvailable;
      if (typeof availabilityOverride.isAvailable !== 'undefined') {
        isAvailable = availabilityOverride.isAvailable;
      }
      return isAvailable;
    }
    render() {
      return _react.default.createElement(WrappedComponent, _extends({}, this.props, {
        isAvailable: this.isAvailable,
        getUnavailableMessage: this.getUnavailableMessage
      }));
    }
  };
  const displayName = getDisplayName(WrappedComponent);
  WithMethodAvailability.displayName = `WithMethodAvailability(${displayName})`;
  return WithMethodAvailability;
};
exports.hoc = withMethodAvailability;
const mapStateToProps = state => {
  const methods = [...state.mfaRegister.availableMethods, ...state.mfaVerify.allMethods];
  const availableMethodOverrides = {};
  Object.values(methods).forEach(method => {
    const {
      urlSegment
    } = method;
    const stateKey = `${urlSegment}Availability`;
    if (typeof state[stateKey] !== 'undefined') {
      availableMethodOverrides[urlSegment] = state[stateKey];
    }
  });
  return {
    availableMethodOverrides
  };
};
const composedWithMethodAvailability = (0, _redux.compose)((0, _reactRedux.connect)(mapStateToProps), withMethodAvailability);
var _default = exports["default"] = composedWithMethodAvailability;

/***/ }),

/***/ "./client/src/state/mfaAdministration/actionTypes.js":
/*!***********************************************************!*\
  !*** ./client/src/state/mfaAdministration/actionTypes.js ***!
  \***********************************************************/
/***/ (function(__unused_webpack_module, exports) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _default = exports["default"] = ['ADD_REGISTERED_METHOD', 'REMOVE_REGISTERED_METHOD', 'SET_DEFAULT_METHOD', 'SET_REGISTERED_METHODS'].reduce((obj, item) => Object.assign(obj, {
  [item]: `MFA_ADMINISTRATION.${item}`
}), {});

/***/ }),

/***/ "./client/src/state/mfaAdministration/actions.js":
/*!*******************************************************!*\
  !*** ./client/src/state/mfaAdministration/actions.js ***!
  \*******************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports.setRegisteredMethods = exports.setDefaultMethod = exports.registerMethod = exports.deregisterMethod = void 0;
var _actionTypes = _interopRequireDefault(__webpack_require__(/*! ./actionTypes */ "./client/src/state/mfaAdministration/actionTypes.js"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
const registerMethod = method => ({
  type: _actionTypes.default.ADD_REGISTERED_METHOD,
  payload: {
    method
  }
});
exports.registerMethod = registerMethod;
const deregisterMethod = method => ({
  type: _actionTypes.default.REMOVE_REGISTERED_METHOD,
  payload: {
    method
  }
});
exports.deregisterMethod = deregisterMethod;
const setDefaultMethod = urlSegment => ({
  type: _actionTypes.default.SET_DEFAULT_METHOD,
  payload: {
    defaultMethod: urlSegment
  }
});
exports.setDefaultMethod = setDefaultMethod;
const setRegisteredMethods = methods => ({
  type: _actionTypes.default.SET_REGISTERED_METHODS,
  payload: {
    methods
  }
});
exports.setRegisteredMethods = setRegisteredMethods;

/***/ }),

/***/ "./client/src/state/mfaAdministration/reducer.js":
/*!*******************************************************!*\
  !*** ./client/src/state/mfaAdministration/reducer.js ***!
  \*******************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = mfaAdministationReducer;
var _actionTypes = _interopRequireDefault(__webpack_require__(/*! ./actionTypes */ "./client/src/state/mfaAdministration/actionTypes.js"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
const initialState = {
  defaultMethod: null,
  registeredMethods: []
};
function mfaAdministationReducer() {
  let state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
  let {
    type,
    payload
  } = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  const getMatcher = method => candidate => candidate.urlSegment === method.urlSegment;
  const {
    registeredMethods
  } = state;
  switch (type) {
    case _actionTypes.default.ADD_REGISTERED_METHOD:
      {
        const {
          method
        } = payload;
        if (!Array.isArray(registeredMethods)) {
          return {
            ...state,
            registeredMethods: [method]
          };
        }
        if (registeredMethods.find(getMatcher(method))) {
          return state;
        }
        registeredMethods.push(method);
        return {
          ...state,
          registeredMethods
        };
      }
    case _actionTypes.default.REMOVE_REGISTERED_METHOD:
      {
        const {
          method
        } = payload;
        const index = registeredMethods.findIndex(getMatcher(method));
        if (index < 0) {
          return state;
        }
        registeredMethods.splice(index, 1);
        const defaultMethodState = registeredMethods.length === 2 ? {
          defaultMethod: registeredMethods.find(() => true).urlSegment
        } : {};
        return {
          ...state,
          ...defaultMethodState,
          registeredMethods: [...registeredMethods]
        };
      }
    case _actionTypes.default.SET_DEFAULT_METHOD:
      {
        return {
          ...state,
          defaultMethod: payload.defaultMethod
        };
      }
    case _actionTypes.default.SET_REGISTERED_METHODS:
      {
        return {
          ...state,
          registeredMethods: payload.methods
        };
      }
    default:
      return state;
  }
}

/***/ }),

/***/ "./client/src/state/mfaRegister/actionTypes.js":
/*!*****************************************************!*\
  !*** ./client/src/state/mfaRegister/actionTypes.js ***!
  \*****************************************************/
/***/ (function(__unused_webpack_module, exports) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _default = exports["default"] = ['ADD_AVAILABLE_METHOD', 'REMOVE_AVAILABLE_METHOD', 'SET_AVAILABLE_METHODS', 'SET_SCREEN', 'SET_METHOD'].reduce((obj, item) => Object.assign(obj, {
  [item]: `MFA_REGISTER.${item}`
}), {});

/***/ }),

/***/ "./client/src/state/mfaRegister/actions.js":
/*!*************************************************!*\
  !*** ./client/src/state/mfaRegister/actions.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports.showScreen = exports.setAvailableMethods = exports.removeAvailableMethod = exports.chooseMethod = exports.addAvailableMethod = void 0;
var _actionTypes = _interopRequireDefault(__webpack_require__(/*! ./actionTypes */ "./client/src/state/mfaRegister/actionTypes.js"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
const showScreen = screen => ({
  type: _actionTypes.default.SET_SCREEN,
  payload: {
    screen
  }
});
exports.showScreen = showScreen;
const chooseMethod = method => ({
  type: _actionTypes.default.SET_METHOD,
  payload: {
    method
  }
});
exports.chooseMethod = chooseMethod;
const setAvailableMethods = methods => ({
  type: _actionTypes.default.SET_AVAILABLE_METHODS,
  payload: {
    availableMethods: methods
  }
});
exports.setAvailableMethods = setAvailableMethods;
const addAvailableMethod = method => ({
  type: _actionTypes.default.ADD_AVAILABLE_METHOD,
  payload: {
    method
  }
});
exports.addAvailableMethod = addAvailableMethod;
const removeAvailableMethod = method => ({
  type: _actionTypes.default.REMOVE_AVAILABLE_METHOD,
  payload: {
    method
  }
});
exports.removeAvailableMethod = removeAvailableMethod;

/***/ }),

/***/ "./client/src/state/mfaRegister/reducer.js":
/*!*************************************************!*\
  !*** ./client/src/state/mfaRegister/reducer.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = mfaRegisterReducer;
var _Register = __webpack_require__(/*! components/Register */ "./client/src/components/Register.js");
var _actionTypes = _interopRequireDefault(__webpack_require__(/*! ./actionTypes */ "./client/src/state/mfaRegister/actionTypes.js"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
const initialState = {
  screen: _Register.SCREEN_INTRODUCTION,
  method: null,
  availableMethods: []
};
function mfaRegisterReducer() {
  let state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
  let {
    type,
    payload
  } = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  switch (type) {
    case _actionTypes.default.SET_SCREEN:
      {
        const {
          screen
        } = payload;
        if (state.method === null && screen === _Register.SCREEN_REGISTER_METHOD) {
          return {
            ...state,
            screen: _Register.SCREEN_CHOOSE_METHOD
          };
        }
        return {
          ...state,
          screen
        };
      }
    case _actionTypes.default.SET_METHOD:
      {
        return {
          ...state,
          method: payload.method
        };
      }
    case _actionTypes.default.SET_AVAILABLE_METHODS:
      {
        return {
          ...state,
          availableMethods: payload.availableMethods
        };
      }
    case _actionTypes.default.ADD_AVAILABLE_METHOD:
      {
        return {
          ...state,
          availableMethods: [...state.availableMethods, payload.method]
        };
      }
    case _actionTypes.default.REMOVE_AVAILABLE_METHOD:
      {
        return {
          ...state,
          availableMethods: state.availableMethods.filter(method => method.urlSegment !== payload.method.urlSegment)
        };
      }
    default:
      return state;
  }
}

/***/ }),

/***/ "./client/src/state/mfaVerify/actionTypes.js":
/*!***************************************************!*\
  !*** ./client/src/state/mfaVerify/actionTypes.js ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, exports) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _default = exports["default"] = ['SET_ALL_METHODS'].reduce((obj, item) => Object.assign(obj, {
  [item]: `MFA_VERIFY.${item}`
}), {});

/***/ }),

/***/ "./client/src/state/mfaVerify/reducer.js":
/*!***********************************************!*\
  !*** ./client/src/state/mfaVerify/reducer.js ***!
  \***********************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = mfaRegisterReducer;
var _actionTypes = _interopRequireDefault(__webpack_require__(/*! ./actionTypes */ "./client/src/state/mfaVerify/actionTypes.js"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
const initialState = {
  allMethods: []
};
function mfaRegisterReducer() {
  let state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : initialState;
  let {
    type,
    payload
  } = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  switch (type) {
    case _actionTypes.default.SET_ALL_METHODS:
      {
        return {
          ...state,
          allMethods: payload.allMethods
        };
      }
    default:
      return state;
  }
}

/***/ }),

/***/ "./client/src/types/availableMethod.js":
/*!*********************************************!*\
  !*** ./client/src/types/availableMethod.js ***!
  \*********************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
var _default = exports["default"] = _propTypes.default.shape({
  urlSegment: _propTypes.default.string,
  name: _propTypes.default.string,
  description: _propTypes.default.string,
  supportLink: _propTypes.default.string,
  supportText: _propTypes.default.string,
  thumbnail: _propTypes.default.string,
  component: _propTypes.default.string,
  isAvailable: _propTypes.default.bool,
  unavailableMessage: _propTypes.default.string
});

/***/ }),

/***/ "./client/src/types/registeredMethod.js":
/*!**********************************************!*\
  !*** ./client/src/types/registeredMethod.js ***!
  \**********************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;
var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
var _default = exports["default"] = _propTypes.default.shape({
  name: _propTypes.default.string,
  urlSegment: _propTypes.default.string,
  isAvailable: _propTypes.default.bool,
  unavailableMessage: _propTypes.default.string,
  component: _propTypes.default.string,
  supportLink: _propTypes.default.string,
  thumbnail: _propTypes.default.string
});

/***/ }),

/***/ "./node_modules/copy-to-clipboard/index.js":
/*!*************************************************!*\
  !*** ./node_modules/copy-to-clipboard/index.js ***!
  \*************************************************/
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

"use strict";


var deselectCurrent = __webpack_require__(/*! toggle-selection */ "./node_modules/toggle-selection/index.js");

var clipboardToIE11Formatting = {
  "text/plain": "Text",
  "text/html": "Url",
  "default": "Text"
}

var defaultMessage = "Copy to clipboard: #{key}, Enter";

function format(message) {
  var copyKey = (/mac os x/i.test(navigator.userAgent) ? "⌘" : "Ctrl") + "+C";
  return message.replace(/#{\s*key\s*}/g, copyKey);
}

function copy(text, options) {
  var debug,
    message,
    reselectPrevious,
    range,
    selection,
    mark,
    success = false;
  if (!options) {
    options = {};
  }
  debug = options.debug || false;
  try {
    reselectPrevious = deselectCurrent();

    range = document.createRange();
    selection = document.getSelection();

    mark = document.createElement("span");
    mark.textContent = text;
    // avoid screen readers from reading out loud the text
    mark.ariaHidden = "true"
    // reset user styles for span element
    mark.style.all = "unset";
    // prevents scrolling to the end of the page
    mark.style.position = "fixed";
    mark.style.top = 0;
    mark.style.clip = "rect(0, 0, 0, 0)";
    // used to preserve spaces and line breaks
    mark.style.whiteSpace = "pre";
    // do not inherit user-select (it may be `none`)
    mark.style.webkitUserSelect = "text";
    mark.style.MozUserSelect = "text";
    mark.style.msUserSelect = "text";
    mark.style.userSelect = "text";
    mark.addEventListener("copy", function(e) {
      e.stopPropagation();
      if (options.format) {
        e.preventDefault();
        if (typeof e.clipboardData === "undefined") { // IE 11
          debug && console.warn("unable to use e.clipboardData");
          debug && console.warn("trying IE specific stuff");
          window.clipboardData.clearData();
          var format = clipboardToIE11Formatting[options.format] || clipboardToIE11Formatting["default"]
          window.clipboardData.setData(format, text);
        } else { // all other browsers
          e.clipboardData.clearData();
          e.clipboardData.setData(options.format, text);
        }
      }
      if (options.onCopy) {
        e.preventDefault();
        options.onCopy(e.clipboardData);
      }
    });

    document.body.appendChild(mark);

    range.selectNodeContents(mark);
    selection.addRange(range);

    var successful = document.execCommand("copy");
    if (!successful) {
      throw new Error("copy command was unsuccessful");
    }
    success = true;
  } catch (err) {
    debug && console.error("unable to copy using execCommand: ", err);
    debug && console.warn("trying IE specific stuff");
    try {
      window.clipboardData.setData(options.format || "text", text);
      options.onCopy && options.onCopy(window.clipboardData);
      success = true;
    } catch (err) {
      debug && console.error("unable to copy using clipboardData: ", err);
      debug && console.error("falling back to prompt");
      message = format("message" in options ? options.message : defaultMessage);
      window.prompt(message, text);
    }
  } finally {
    if (selection) {
      if (typeof selection.removeRange == "function") {
        selection.removeRange(range);
      } else {
        selection.removeAllRanges();
      }
    }

    if (mark) {
      document.body.removeChild(mark);
    }
    reselectPrevious();
  }

  return success;
}

module.exports = copy;


/***/ }),

/***/ "./node_modules/printd/index.js":
/*!**************************************!*\
  !*** ./node_modules/printd/index.js ***!
  \**************************************/
/***/ (function(__unused_webpack_module, exports) {

"use strict";

Object.defineProperty(exports, "__esModule", ({ value: true }));
exports.Printd = exports.createIFrame = exports.createLinkStyle = exports.createStyle = void 0;
var URL_LONG = /^(((http[s]?)|file):)?(\/\/)+([0-9a-zA-Z-_.=?&].+)$/;
var URL_SHORT = /^((\.|\.\.)?\/)([0-9a-zA-Z-_.=?&]+\/)*([0-9a-zA-Z-_.=?&]+)$/;
var isValidURL = function (str) { return URL_LONG.test(str) || URL_SHORT.test(str); };
function createStyle(doc, cssText) {
    var style = doc.createElement("style");
    style.appendChild(doc.createTextNode(cssText));
    return style;
}
exports.createStyle = createStyle;
function createLinkStyle(doc, url) {
    var style = doc.createElement("link");
    style.type = "text/css";
    style.rel = "stylesheet";
    style.href = url;
    return style;
}
exports.createLinkStyle = createLinkStyle;
function createIFrame(parent) {
    var el = window.document.createElement("iframe");
    el.setAttribute("src", "about:blank");
    el.setAttribute("style", "visibility:hidden;width:0;height:0;position:absolute;z-index:-9999;bottom:0;");
    el.setAttribute("width", "0");
    el.setAttribute("height", "0");
    el.setAttribute("wmode", "opaque");
    parent.appendChild(el);
    return el;
}
exports.createIFrame = createIFrame;
var DEFAULT_OPTIONS = {
    parent: window.document.body,
    headElements: [],
    bodyElements: []
};
/** Printd class that prints HTML elements in a blank document */
var Printd = /** @class */ (function () {
    function Printd(options) {
        this.isLoading = false;
        this.hasEvents = false;
        // IE 11+ "Object.assign" polyfill
        this.opts = [DEFAULT_OPTIONS, options || {}].reduce(function (a, b) {
            Object.keys(b).forEach(function (k) { return (a[k] = b[k]); });
            return a;
        }, {});
        this.iframe = createIFrame(this.opts.parent);
    }
    /** Gets current Iframe reference */
    Printd.prototype.getIFrame = function () {
        return this.iframe;
    };
    /**
     * Print an HTMLElement
     *
     * @param el HTMLElement
     * @param styles Optional styles (css texts or urls) that will add to iframe document.head
     * @param scripts Optional scripts (script texts or urls) that will add to iframe document.body
     * @param callback Optional callback that will be triggered when content is ready to print
     */
    Printd.prototype.print = function (el, styles, scripts, callback) {
        if (this.isLoading)
            return;
        var _a = this.iframe, contentDocument = _a.contentDocument, contentWindow = _a.contentWindow;
        if (!contentDocument || !contentWindow)
            return;
        this.iframe.src = "about:blank";
        this.elCopy = el.cloneNode(true);
        if (!this.elCopy)
            return;
        this.isLoading = true;
        this.callback = callback;
        var doc = contentWindow.document;
        doc.open();
        doc.write('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>');
        this.addEvents();
        // 1. append custom elements
        var _b = this.opts, headElements = _b.headElements, bodyElements = _b.bodyElements;
        // 1.1 append custom head elements
        if (Array.isArray(headElements)) {
            headElements.forEach(function (el) { return doc.head.appendChild(el); });
        }
        // 1.1 append custom body elements
        if (Array.isArray(bodyElements)) {
            bodyElements.forEach(function (el) { return doc.body.appendChild(el); });
        }
        // 2. append custom styles
        if (Array.isArray(styles)) {
            styles.forEach(function (value) {
                if (value) {
                    doc.head.appendChild(isValidURL(value) ? createLinkStyle(doc, value) : createStyle(doc, value));
                }
            });
        }
        // 3. append element copy
        doc.body.appendChild(this.elCopy);
        // 4. append custom scripts
        if (Array.isArray(scripts)) {
            scripts.forEach(function (value) {
                if (value) {
                    var script = doc.createElement("script");
                    if (isValidURL(value)) {
                        script.src = value;
                    }
                    else {
                        script.innerText = value;
                    }
                    doc.body.appendChild(script);
                }
            });
        }
        doc.close();
    };
    /**
     * Print an URL
     *
     * @param url URL to print
     * @param callback Optional callback that will be triggered when content is ready to print
     */
    Printd.prototype.printURL = function (url, callback) {
        if (this.isLoading)
            return;
        this.addEvents();
        this.isLoading = true;
        this.callback = callback;
        this.iframe.src = url;
    };
    /**
     * Add a browser `beforeprint` print event listener providing a custom callback.
     *
     * Note that it only works when printing custom HTML elements.
     *
     */
    Printd.prototype.onBeforePrint = function (callback) {
        this.onbeforeprint = callback;
    };
    /**
     * Add a browser `afterprint` print event listener providing a custom callback.
     *
     * Note that it only works when printing custom HTML elements.
     *
     */
    Printd.prototype.onAfterPrint = function (callback) {
        this.onafterprint = callback;
    };
    Printd.prototype.launchPrint = function (contentWindow) {
        if (!this.isLoading) {
            contentWindow.print();
        }
    };
    Printd.prototype.addEvents = function () {
        var _this = this;
        if (!this.hasEvents) {
            this.hasEvents = true;
            this.iframe.addEventListener("load", function () { return _this.onLoad(); }, false);
            var contentWindow = this.iframe.contentWindow;
            if (contentWindow) {
                if (this.onbeforeprint) {
                    contentWindow.addEventListener("beforeprint", this.onbeforeprint);
                }
                if (this.onafterprint) {
                    contentWindow.addEventListener("afterprint", this.onafterprint);
                }
            }
        }
    };
    Printd.prototype.onLoad = function () {
        var _this = this;
        if (this.iframe) {
            this.isLoading = false;
            var _a = this.iframe, contentDocument = _a.contentDocument, contentWindow_1 = _a.contentWindow;
            if (!contentDocument || !contentWindow_1)
                return;
            if (typeof this.callback === "function") {
                this.callback({
                    iframe: this.iframe,
                    element: this.elCopy,
                    launchPrint: function () { return _this.launchPrint(contentWindow_1); }
                });
            }
            else {
                this.launchPrint(contentWindow_1);
            }
        }
    };
    return Printd;
}());
exports.Printd = Printd;
exports["default"] = Printd;


/***/ }),

/***/ "./node_modules/react-copy-to-clipboard/lib/Component.js":
/*!***************************************************************!*\
  !*** ./node_modules/react-copy-to-clipboard/lib/Component.js ***!
  \***************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports.CopyToClipboard = void 0;

var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));

var _copyToClipboard = _interopRequireDefault(__webpack_require__(/*! copy-to-clipboard */ "./node_modules/copy-to-clipboard/index.js"));

var _excluded = ["text", "onCopy", "options", "children"];

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); Object.defineProperty(subClass, "prototype", { writable: false }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var CopyToClipboard = /*#__PURE__*/function (_React$PureComponent) {
  _inherits(CopyToClipboard, _React$PureComponent);

  var _super = _createSuper(CopyToClipboard);

  function CopyToClipboard() {
    var _this;

    _classCallCheck(this, CopyToClipboard);

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    _this = _super.call.apply(_super, [this].concat(args));

    _defineProperty(_assertThisInitialized(_this), "onClick", function (event) {
      var _this$props = _this.props,
          text = _this$props.text,
          onCopy = _this$props.onCopy,
          children = _this$props.children,
          options = _this$props.options;

      var elem = _react["default"].Children.only(children);

      var result = (0, _copyToClipboard["default"])(text, options);

      if (onCopy) {
        onCopy(text, result);
      } // Bypass onClick if it was present


      if (elem && elem.props && typeof elem.props.onClick === 'function') {
        elem.props.onClick(event);
      }
    });

    return _this;
  }

  _createClass(CopyToClipboard, [{
    key: "render",
    value: function render() {
      var _this$props2 = this.props,
          _text = _this$props2.text,
          _onCopy = _this$props2.onCopy,
          _options = _this$props2.options,
          children = _this$props2.children,
          props = _objectWithoutProperties(_this$props2, _excluded);

      var elem = _react["default"].Children.only(children);

      return /*#__PURE__*/_react["default"].cloneElement(elem, _objectSpread(_objectSpread({}, props), {}, {
        onClick: this.onClick
      }));
    }
  }]);

  return CopyToClipboard;
}(_react["default"].PureComponent);

exports.CopyToClipboard = CopyToClipboard;

_defineProperty(CopyToClipboard, "defaultProps", {
  onCopy: undefined,
  options: undefined
});

/***/ }),

/***/ "./node_modules/react-copy-to-clipboard/lib/index.js":
/*!***********************************************************!*\
  !*** ./node_modules/react-copy-to-clipboard/lib/index.js ***!
  \***********************************************************/
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

"use strict";


var _require = __webpack_require__(/*! ./Component */ "./node_modules/react-copy-to-clipboard/lib/Component.js"),
    CopyToClipboard = _require.CopyToClipboard;

CopyToClipboard.CopyToClipboard = CopyToClipboard;
module.exports = CopyToClipboard;

/***/ }),

/***/ "./node_modules/reactstrap-confirm/dist/components/ConfirmModal.js":
/*!*************************************************************************!*\
  !*** ./node_modules/reactstrap-confirm/dist/components/ConfirmModal.js ***!
  \*************************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;

var _react = _interopRequireWildcard(__webpack_require__(/*! react */ "react"));

var _propTypes = _interopRequireDefault(__webpack_require__(/*! prop-types */ "prop-types"));

var _reactstrap = __webpack_require__(/*! reactstrap */ "reactstrap");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _getRequireWildcardCache(nodeInterop) { if (typeof WeakMap !== "function") return null; var cacheBabelInterop = new WeakMap(); var cacheNodeInterop = new WeakMap(); return (_getRequireWildcardCache = function _getRequireWildcardCache(nodeInterop) { return nodeInterop ? cacheNodeInterop : cacheBabelInterop; })(nodeInterop); }

function _interopRequireWildcard(obj, nodeInterop) { if (!nodeInterop && obj && obj.__esModule) { return obj; } if (obj === null || _typeof(obj) !== "object" && typeof obj !== "function") { return { "default": obj }; } var cache = _getRequireWildcardCache(nodeInterop); if (cache && cache.has(obj)) { return cache.get(obj); } var newObj = {}; var hasPropertyDescriptor = Object.defineProperty && Object.getOwnPropertyDescriptor; for (var key in obj) { if (key !== "default" && Object.prototype.hasOwnProperty.call(obj, key)) { var desc = hasPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : null; if (desc && (desc.get || desc.set)) { Object.defineProperty(newObj, key, desc); } else { newObj[key] = obj[key]; } } } newObj["default"] = obj; if (cache) { cache.set(obj, newObj); } return newObj; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

var ConfirmModal = function ConfirmModal(_ref) {
  var onClose = _ref.onClose,
      message = _ref.message,
      title = _ref.title,
      confirmText = _ref.confirmText,
      cancelText = _ref.cancelText,
      confirmColor = _ref.confirmColor,
      cancelColor = _ref.cancelColor,
      className = _ref.className,
      buttonsComponent = _ref.buttonsComponent,
      size = _ref.size,
      bodyComponent = _ref.bodyComponent,
      modalProps = _ref.modalProps;

  var buttonsContent = /*#__PURE__*/_react["default"].createElement(_react.Fragment, null, cancelText && /*#__PURE__*/_react["default"].createElement(_reactstrap.Button, {
    color: cancelColor,
    onClick: function onClick() {
      return onClose(false);
    }
  }, cancelText), ' ', /*#__PURE__*/_react["default"].createElement(_reactstrap.Button, {
    color: confirmColor,
    onClick: function onClick() {
      return onClose(true);
    }
  }, confirmText));

  if (buttonsComponent) {
    var CustomComponent = buttonsComponent;
    buttonsContent = /*#__PURE__*/_react["default"].createElement(CustomComponent, {
      onClose: onClose
    });
  }

  var BodyComponent = bodyComponent;
  return /*#__PURE__*/_react["default"].createElement(_reactstrap.Modal, _extends({
    size: size,
    isOpen: true,
    toggle: function toggle() {
      return onClose(false);
    },
    className: "reactstrap-confirm ".concat(className)
  }, modalProps), title && /*#__PURE__*/_react["default"].createElement(_reactstrap.ModalHeader, {
    toggle: function toggle() {
      return onClose(false);
    }
  }, title || null), /*#__PURE__*/_react["default"].createElement(_reactstrap.ModalBody, null, bodyComponent ? /*#__PURE__*/_react["default"].createElement(BodyComponent, null) : message), /*#__PURE__*/_react["default"].createElement(_reactstrap.ModalFooter, null, buttonsContent));
};

ConfirmModal.defaultProps = {
  message: 'Are you sure?',
  title: 'Warning!',
  confirmText: 'Ok',
  cancelText: 'Cancel',
  confirmColor: 'primary',
  cancelColor: '',
  className: '',
  buttonsComponent: null,
  size: null,
  bodyComponent: null,
  modalProps: {}
};
ConfirmModal.propTypes = {
  onClose: _propTypes["default"].func.isRequired,
  message: _propTypes["default"].node,
  title: _propTypes["default"].node,
  confirmText: _propTypes["default"].node,
  cancelText: _propTypes["default"].node,
  confirmColor: _propTypes["default"].string,
  cancelColor: _propTypes["default"].string,
  className: _propTypes["default"].string,
  size: _propTypes["default"].string,
  buttonsComponent: _propTypes["default"].func,
  bodyComponent: _propTypes["default"].func,
  modalProps: _propTypes["default"].object
};
var _default = ConfirmModal;
exports["default"] = _default;

/***/ }),

/***/ "./node_modules/reactstrap-confirm/dist/index.js":
/*!*******************************************************!*\
  !*** ./node_modules/reactstrap-confirm/dist/index.js ***!
  \*******************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", ({
  value: true
}));
exports["default"] = void 0;

var _react = _interopRequireDefault(__webpack_require__(/*! react */ "react"));

var _reactDom = __webpack_require__(/*! react-dom */ "react-dom");

var _ConfirmModal = _interopRequireDefault(__webpack_require__(/*! ./components/ConfirmModal */ "./node_modules/reactstrap-confirm/dist/components/ConfirmModal.js"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

var confirm = function confirm(props) {
  return new Promise(function (resolve) {
    var el = document.createElement('div');

    var handleResolve = function handleResolve(result) {
      (0, _reactDom.unmountComponentAtNode)(el);
      el = null;
      resolve(result);
    };

    (0, _reactDom.render)( /*#__PURE__*/_react["default"].createElement(_ConfirmModal["default"], _extends({}, props, {
      onClose: handleResolve
    })), el);
  });
};

var _default = confirm;
exports["default"] = _default;

/***/ }),

/***/ "./node_modules/regenerator-runtime/runtime.js":
/*!*****************************************************!*\
  !*** ./node_modules/regenerator-runtime/runtime.js ***!
  \*****************************************************/
/***/ (function(module) {

/**
 * Copyright (c) 2014-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

var runtime = (function (exports) {
  "use strict";

  var Op = Object.prototype;
  var hasOwn = Op.hasOwnProperty;
  var defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; };
  var undefined; // More compressible than void 0.
  var $Symbol = typeof Symbol === "function" ? Symbol : {};
  var iteratorSymbol = $Symbol.iterator || "@@iterator";
  var asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator";
  var toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag";

  function define(obj, key, value) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
    return obj[key];
  }
  try {
    // IE 8 has a broken Object.defineProperty that only works on DOM objects.
    define({}, "");
  } catch (err) {
    define = function(obj, key, value) {
      return obj[key] = value;
    };
  }

  function wrap(innerFn, outerFn, self, tryLocsList) {
    // If outerFn provided and outerFn.prototype is a Generator, then outerFn.prototype instanceof Generator.
    var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator;
    var generator = Object.create(protoGenerator.prototype);
    var context = new Context(tryLocsList || []);

    // The ._invoke method unifies the implementations of the .next,
    // .throw, and .return methods.
    defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) });

    return generator;
  }
  exports.wrap = wrap;

  // Try/catch helper to minimize deoptimizations. Returns a completion
  // record like context.tryEntries[i].completion. This interface could
  // have been (and was previously) designed to take a closure to be
  // invoked without arguments, but in all the cases we care about we
  // already have an existing method we want to call, so there's no need
  // to create a new function object. We can even get away with assuming
  // the method takes exactly one argument, since that happens to be true
  // in every case, so we don't have to touch the arguments object. The
  // only additional allocation required is the completion record, which
  // has a stable shape and so hopefully should be cheap to allocate.
  function tryCatch(fn, obj, arg) {
    try {
      return { type: "normal", arg: fn.call(obj, arg) };
    } catch (err) {
      return { type: "throw", arg: err };
    }
  }

  var GenStateSuspendedStart = "suspendedStart";
  var GenStateSuspendedYield = "suspendedYield";
  var GenStateExecuting = "executing";
  var GenStateCompleted = "completed";

  // Returning this object from the innerFn has the same effect as
  // breaking out of the dispatch switch statement.
  var ContinueSentinel = {};

  // Dummy constructor functions that we use as the .constructor and
  // .constructor.prototype properties for functions that return Generator
  // objects. For full spec compliance, you may wish to configure your
  // minifier not to mangle the names of these two functions.
  function Generator() {}
  function GeneratorFunction() {}
  function GeneratorFunctionPrototype() {}

  // This is a polyfill for %IteratorPrototype% for environments that
  // don't natively support it.
  var IteratorPrototype = {};
  define(IteratorPrototype, iteratorSymbol, function () {
    return this;
  });

  var getProto = Object.getPrototypeOf;
  var NativeIteratorPrototype = getProto && getProto(getProto(values([])));
  if (NativeIteratorPrototype &&
      NativeIteratorPrototype !== Op &&
      hasOwn.call(NativeIteratorPrototype, iteratorSymbol)) {
    // This environment has a native %IteratorPrototype%; use it instead
    // of the polyfill.
    IteratorPrototype = NativeIteratorPrototype;
  }

  var Gp = GeneratorFunctionPrototype.prototype =
    Generator.prototype = Object.create(IteratorPrototype);
  GeneratorFunction.prototype = GeneratorFunctionPrototype;
  defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: true });
  defineProperty(
    GeneratorFunctionPrototype,
    "constructor",
    { value: GeneratorFunction, configurable: true }
  );
  GeneratorFunction.displayName = define(
    GeneratorFunctionPrototype,
    toStringTagSymbol,
    "GeneratorFunction"
  );

  // Helper for defining the .next, .throw, and .return methods of the
  // Iterator interface in terms of a single ._invoke method.
  function defineIteratorMethods(prototype) {
    ["next", "throw", "return"].forEach(function(method) {
      define(prototype, method, function(arg) {
        return this._invoke(method, arg);
      });
    });
  }

  exports.isGeneratorFunction = function(genFun) {
    var ctor = typeof genFun === "function" && genFun.constructor;
    return ctor
      ? ctor === GeneratorFunction ||
        // For the native GeneratorFunction constructor, the best we can
        // do is to check its .name property.
        (ctor.displayName || ctor.name) === "GeneratorFunction"
      : false;
  };

  exports.mark = function(genFun) {
    if (Object.setPrototypeOf) {
      Object.setPrototypeOf(genFun, GeneratorFunctionPrototype);
    } else {
      genFun.__proto__ = GeneratorFunctionPrototype;
      define(genFun, toStringTagSymbol, "GeneratorFunction");
    }
    genFun.prototype = Object.create(Gp);
    return genFun;
  };

  // Within the body of any async function, `await x` is transformed to
  // `yield regeneratorRuntime.awrap(x)`, so that the runtime can test
  // `hasOwn.call(value, "__await")` to determine if the yielded value is
  // meant to be awaited.
  exports.awrap = function(arg) {
    return { __await: arg };
  };

  function AsyncIterator(generator, PromiseImpl) {
    function invoke(method, arg, resolve, reject) {
      var record = tryCatch(generator[method], generator, arg);
      if (record.type === "throw") {
        reject(record.arg);
      } else {
        var result = record.arg;
        var value = result.value;
        if (value &&
            typeof value === "object" &&
            hasOwn.call(value, "__await")) {
          return PromiseImpl.resolve(value.__await).then(function(value) {
            invoke("next", value, resolve, reject);
          }, function(err) {
            invoke("throw", err, resolve, reject);
          });
        }

        return PromiseImpl.resolve(value).then(function(unwrapped) {
          // When a yielded Promise is resolved, its final value becomes
          // the .value of the Promise<{value,done}> result for the
          // current iteration.
          result.value = unwrapped;
          resolve(result);
        }, function(error) {
          // If a rejected Promise was yielded, throw the rejection back
          // into the async generator function so it can be handled there.
          return invoke("throw", error, resolve, reject);
        });
      }
    }

    var previousPromise;

    function enqueue(method, arg) {
      function callInvokeWithMethodAndArg() {
        return new PromiseImpl(function(resolve, reject) {
          invoke(method, arg, resolve, reject);
        });
      }

      return previousPromise =
        // If enqueue has been called before, then we want to wait until
        // all previous Promises have been resolved before calling invoke,
        // so that results are always delivered in the correct order. If
        // enqueue has not been called before, then it is important to
        // call invoke immediately, without waiting on a callback to fire,
        // so that the async generator function has the opportunity to do
        // any necessary setup in a predictable way. This predictability
        // is why the Promise constructor synchronously invokes its
        // executor callback, and why async functions synchronously
        // execute code before the first await. Since we implement simple
        // async functions in terms of async generators, it is especially
        // important to get this right, even though it requires care.
        previousPromise ? previousPromise.then(
          callInvokeWithMethodAndArg,
          // Avoid propagating failures to Promises returned by later
          // invocations of the iterator.
          callInvokeWithMethodAndArg
        ) : callInvokeWithMethodAndArg();
    }

    // Define the unified helper method that is used to implement .next,
    // .throw, and .return (see defineIteratorMethods).
    defineProperty(this, "_invoke", { value: enqueue });
  }

  defineIteratorMethods(AsyncIterator.prototype);
  define(AsyncIterator.prototype, asyncIteratorSymbol, function () {
    return this;
  });
  exports.AsyncIterator = AsyncIterator;

  // Note that simple async functions are implemented on top of
  // AsyncIterator objects; they just return a Promise for the value of
  // the final result produced by the iterator.
  exports.async = function(innerFn, outerFn, self, tryLocsList, PromiseImpl) {
    if (PromiseImpl === void 0) PromiseImpl = Promise;

    var iter = new AsyncIterator(
      wrap(innerFn, outerFn, self, tryLocsList),
      PromiseImpl
    );

    return exports.isGeneratorFunction(outerFn)
      ? iter // If outerFn is a generator, return the full iterator.
      : iter.next().then(function(result) {
          return result.done ? result.value : iter.next();
        });
  };

  function makeInvokeMethod(innerFn, self, context) {
    var state = GenStateSuspendedStart;

    return function invoke(method, arg) {
      if (state === GenStateExecuting) {
        throw new Error("Generator is already running");
      }

      if (state === GenStateCompleted) {
        if (method === "throw") {
          throw arg;
        }

        // Be forgiving, per 25.3.3.3.3 of the spec:
        // https://people.mozilla.org/~jorendorff/es6-draft.html#sec-generatorresume
        return doneResult();
      }

      context.method = method;
      context.arg = arg;

      while (true) {
        var delegate = context.delegate;
        if (delegate) {
          var delegateResult = maybeInvokeDelegate(delegate, context);
          if (delegateResult) {
            if (delegateResult === ContinueSentinel) continue;
            return delegateResult;
          }
        }

        if (context.method === "next") {
          // Setting context._sent for legacy support of Babel's
          // function.sent implementation.
          context.sent = context._sent = context.arg;

        } else if (context.method === "throw") {
          if (state === GenStateSuspendedStart) {
            state = GenStateCompleted;
            throw context.arg;
          }

          context.dispatchException(context.arg);

        } else if (context.method === "return") {
          context.abrupt("return", context.arg);
        }

        state = GenStateExecuting;

        var record = tryCatch(innerFn, self, context);
        if (record.type === "normal") {
          // If an exception is thrown from innerFn, we leave state ===
          // GenStateExecuting and loop back for another invocation.
          state = context.done
            ? GenStateCompleted
            : GenStateSuspendedYield;

          if (record.arg === ContinueSentinel) {
            continue;
          }

          return {
            value: record.arg,
            done: context.done
          };

        } else if (record.type === "throw") {
          state = GenStateCompleted;
          // Dispatch the exception by looping back around to the
          // context.dispatchException(context.arg) call above.
          context.method = "throw";
          context.arg = record.arg;
        }
      }
    };
  }

  // Call delegate.iterator[context.method](context.arg) and handle the
  // result, either by returning a { value, done } result from the
  // delegate iterator, or by modifying context.method and context.arg,
  // setting context.delegate to null, and returning the ContinueSentinel.
  function maybeInvokeDelegate(delegate, context) {
    var methodName = context.method;
    var method = delegate.iterator[methodName];
    if (method === undefined) {
      // A .throw or .return when the delegate iterator has no .throw
      // method, or a missing .next mehtod, always terminate the
      // yield* loop.
      context.delegate = null;

      // Note: ["return"] must be used for ES3 parsing compatibility.
      if (methodName === "throw" && delegate.iterator["return"]) {
        // If the delegate iterator has a return method, give it a
        // chance to clean up.
        context.method = "return";
        context.arg = undefined;
        maybeInvokeDelegate(delegate, context);

        if (context.method === "throw") {
          // If maybeInvokeDelegate(context) changed context.method from
          // "return" to "throw", let that override the TypeError below.
          return ContinueSentinel;
        }
      }
      if (methodName !== "return") {
        context.method = "throw";
        context.arg = new TypeError(
          "The iterator does not provide a '" + methodName + "' method");
      }

      return ContinueSentinel;
    }

    var record = tryCatch(method, delegate.iterator, context.arg);

    if (record.type === "throw") {
      context.method = "throw";
      context.arg = record.arg;
      context.delegate = null;
      return ContinueSentinel;
    }

    var info = record.arg;

    if (! info) {
      context.method = "throw";
      context.arg = new TypeError("iterator result is not an object");
      context.delegate = null;
      return ContinueSentinel;
    }

    if (info.done) {
      // Assign the result of the finished delegate to the temporary
      // variable specified by delegate.resultName (see delegateYield).
      context[delegate.resultName] = info.value;

      // Resume execution at the desired location (see delegateYield).
      context.next = delegate.nextLoc;

      // If context.method was "throw" but the delegate handled the
      // exception, let the outer generator proceed normally. If
      // context.method was "next", forget context.arg since it has been
      // "consumed" by the delegate iterator. If context.method was
      // "return", allow the original .return call to continue in the
      // outer generator.
      if (context.method !== "return") {
        context.method = "next";
        context.arg = undefined;
      }

    } else {
      // Re-yield the result returned by the delegate method.
      return info;
    }

    // The delegate iterator is finished, so forget it and continue with
    // the outer generator.
    context.delegate = null;
    return ContinueSentinel;
  }

  // Define Generator.prototype.{next,throw,return} in terms of the
  // unified ._invoke helper method.
  defineIteratorMethods(Gp);

  define(Gp, toStringTagSymbol, "Generator");

  // A Generator should always return itself as the iterator object when the
  // @@iterator function is called on it. Some browsers' implementations of the
  // iterator prototype chain incorrectly implement this, causing the Generator
  // object to not be returned from this call. This ensures that doesn't happen.
  // See https://github.com/facebook/regenerator/issues/274 for more details.
  define(Gp, iteratorSymbol, function() {
    return this;
  });

  define(Gp, "toString", function() {
    return "[object Generator]";
  });

  function pushTryEntry(locs) {
    var entry = { tryLoc: locs[0] };

    if (1 in locs) {
      entry.catchLoc = locs[1];
    }

    if (2 in locs) {
      entry.finallyLoc = locs[2];
      entry.afterLoc = locs[3];
    }

    this.tryEntries.push(entry);
  }

  function resetTryEntry(entry) {
    var record = entry.completion || {};
    record.type = "normal";
    delete record.arg;
    entry.completion = record;
  }

  function Context(tryLocsList) {
    // The root entry object (effectively a try statement without a catch
    // or a finally block) gives us a place to store values thrown from
    // locations where there is no enclosing try statement.
    this.tryEntries = [{ tryLoc: "root" }];
    tryLocsList.forEach(pushTryEntry, this);
    this.reset(true);
  }

  exports.keys = function(val) {
    var object = Object(val);
    var keys = [];
    for (var key in object) {
      keys.push(key);
    }
    keys.reverse();

    // Rather than returning an object with a next method, we keep
    // things simple and return the next function itself.
    return function next() {
      while (keys.length) {
        var key = keys.pop();
        if (key in object) {
          next.value = key;
          next.done = false;
          return next;
        }
      }

      // To avoid creating an additional object, we just hang the .value
      // and .done properties off the next function object itself. This
      // also ensures that the minifier will not anonymize the function.
      next.done = true;
      return next;
    };
  };

  function values(iterable) {
    if (iterable) {
      var iteratorMethod = iterable[iteratorSymbol];
      if (iteratorMethod) {
        return iteratorMethod.call(iterable);
      }

      if (typeof iterable.next === "function") {
        return iterable;
      }

      if (!isNaN(iterable.length)) {
        var i = -1, next = function next() {
          while (++i < iterable.length) {
            if (hasOwn.call(iterable, i)) {
              next.value = iterable[i];
              next.done = false;
              return next;
            }
          }

          next.value = undefined;
          next.done = true;

          return next;
        };

        return next.next = next;
      }
    }

    // Return an iterator with no values.
    return { next: doneResult };
  }
  exports.values = values;

  function doneResult() {
    return { value: undefined, done: true };
  }

  Context.prototype = {
    constructor: Context,

    reset: function(skipTempReset) {
      this.prev = 0;
      this.next = 0;
      // Resetting context._sent for legacy support of Babel's
      // function.sent implementation.
      this.sent = this._sent = undefined;
      this.done = false;
      this.delegate = null;

      this.method = "next";
      this.arg = undefined;

      this.tryEntries.forEach(resetTryEntry);

      if (!skipTempReset) {
        for (var name in this) {
          // Not sure about the optimal order of these conditions:
          if (name.charAt(0) === "t" &&
              hasOwn.call(this, name) &&
              !isNaN(+name.slice(1))) {
            this[name] = undefined;
          }
        }
      }
    },

    stop: function() {
      this.done = true;

      var rootEntry = this.tryEntries[0];
      var rootRecord = rootEntry.completion;
      if (rootRecord.type === "throw") {
        throw rootRecord.arg;
      }

      return this.rval;
    },

    dispatchException: function(exception) {
      if (this.done) {
        throw exception;
      }

      var context = this;
      function handle(loc, caught) {
        record.type = "throw";
        record.arg = exception;
        context.next = loc;

        if (caught) {
          // If the dispatched exception was caught by a catch block,
          // then let that catch block handle the exception normally.
          context.method = "next";
          context.arg = undefined;
        }

        return !! caught;
      }

      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        var record = entry.completion;

        if (entry.tryLoc === "root") {
          // Exception thrown outside of any try block that could handle
          // it, so set the completion value of the entire function to
          // throw the exception.
          return handle("end");
        }

        if (entry.tryLoc <= this.prev) {
          var hasCatch = hasOwn.call(entry, "catchLoc");
          var hasFinally = hasOwn.call(entry, "finallyLoc");

          if (hasCatch && hasFinally) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            } else if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }

          } else if (hasCatch) {
            if (this.prev < entry.catchLoc) {
              return handle(entry.catchLoc, true);
            }

          } else if (hasFinally) {
            if (this.prev < entry.finallyLoc) {
              return handle(entry.finallyLoc);
            }

          } else {
            throw new Error("try statement without catch or finally");
          }
        }
      }
    },

    abrupt: function(type, arg) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.tryLoc <= this.prev &&
            hasOwn.call(entry, "finallyLoc") &&
            this.prev < entry.finallyLoc) {
          var finallyEntry = entry;
          break;
        }
      }

      if (finallyEntry &&
          (type === "break" ||
           type === "continue") &&
          finallyEntry.tryLoc <= arg &&
          arg <= finallyEntry.finallyLoc) {
        // Ignore the finally entry if control is not jumping to a
        // location outside the try/catch block.
        finallyEntry = null;
      }

      var record = finallyEntry ? finallyEntry.completion : {};
      record.type = type;
      record.arg = arg;

      if (finallyEntry) {
        this.method = "next";
        this.next = finallyEntry.finallyLoc;
        return ContinueSentinel;
      }

      return this.complete(record);
    },

    complete: function(record, afterLoc) {
      if (record.type === "throw") {
        throw record.arg;
      }

      if (record.type === "break" ||
          record.type === "continue") {
        this.next = record.arg;
      } else if (record.type === "return") {
        this.rval = this.arg = record.arg;
        this.method = "return";
        this.next = "end";
      } else if (record.type === "normal" && afterLoc) {
        this.next = afterLoc;
      }

      return ContinueSentinel;
    },

    finish: function(finallyLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.finallyLoc === finallyLoc) {
          this.complete(entry.completion, entry.afterLoc);
          resetTryEntry(entry);
          return ContinueSentinel;
        }
      }
    },

    "catch": function(tryLoc) {
      for (var i = this.tryEntries.length - 1; i >= 0; --i) {
        var entry = this.tryEntries[i];
        if (entry.tryLoc === tryLoc) {
          var record = entry.completion;
          if (record.type === "throw") {
            var thrown = record.arg;
            resetTryEntry(entry);
          }
          return thrown;
        }
      }

      // The context.catch method must only be called with a location
      // argument that corresponds to a known catch block.
      throw new Error("illegal catch attempt");
    },

    delegateYield: function(iterable, resultName, nextLoc) {
      this.delegate = {
        iterator: values(iterable),
        resultName: resultName,
        nextLoc: nextLoc
      };

      if (this.method === "next") {
        // Deliberately forget the last sent value so that we don't
        // accidentally pass it on to the delegate.
        this.arg = undefined;
      }

      return ContinueSentinel;
    }
  };

  // Regardless of whether this script is executing as a CommonJS module
  // or not, return the runtime object so that we can declare the variable
  // regeneratorRuntime in the outer scope, which allows this module to be
  // injected easily by `bin/regenerator --include-runtime script.js`.
  return exports;

}(
  // If this script is executing as a CommonJS module, use module.exports
  // as the regeneratorRuntime namespace. Otherwise create a new empty
  // object. Either way, the resulting object will be used to initialize
  // the regeneratorRuntime variable at the top of this file.
   true ? module.exports : 0
));

try {
  regeneratorRuntime = runtime;
} catch (accidentalStrictMode) {
  // This module should not be running in strict mode, so the above
  // assignment should always work unless something is misconfigured. Just
  // in case runtime.js accidentally runs in strict mode, in modern engines
  // we can explicitly access globalThis. In older engines we can escape
  // strict mode using a global Function call. This could conceivably fail
  // if a Content Security Policy forbids using Function, but in that case
  // the proper solution is to fix the accidental strict mode problem. If
  // you've misconfigured your bundler to force strict mode and applied a
  // CSP to forbid Function, and you're not willing to fix either of those
  // problems, please detail your unique predicament in a GitHub issue.
  if (typeof globalThis === "object") {
    globalThis.regeneratorRuntime = runtime;
  } else {
    Function("r", "regeneratorRuntime = r")(runtime);
  }
}


/***/ }),

/***/ "./node_modules/toggle-selection/index.js":
/*!************************************************!*\
  !*** ./node_modules/toggle-selection/index.js ***!
  \************************************************/
/***/ (function(module) {


module.exports = function () {
  var selection = document.getSelection();
  if (!selection.rangeCount) {
    return function () {};
  }
  var active = document.activeElement;

  var ranges = [];
  for (var i = 0; i < selection.rangeCount; i++) {
    ranges.push(selection.getRangeAt(i));
  }

  switch (active.tagName.toUpperCase()) { // .toUpperCase handles XHTML
    case 'INPUT':
    case 'TEXTAREA':
      active.blur();
      break;

    default:
      active = null;
      break;
  }

  selection.removeAllRanges();
  return function () {
    selection.type === 'Caret' &&
    selection.removeAllRanges();

    if (!selection.rangeCount) {
      ranges.forEach(function(range) {
        selection.addRange(range);
      });
    }

    active &&
    active.focus();
  };
};


/***/ }),

/***/ "lib/Config":
/*!*************************!*\
  !*** external "Config" ***!
  \*************************/
/***/ (function(module) {

"use strict";
module.exports = Config;

/***/ }),

/***/ "lib/Injector":
/*!***************************!*\
  !*** external "Injector" ***!
  \***************************/
/***/ (function(module) {

"use strict";
module.exports = Injector;

/***/ }),

/***/ "prop-types":
/*!****************************!*\
  !*** external "PropTypes" ***!
  \****************************/
/***/ (function(module) {

"use strict";
module.exports = PropTypes;

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ (function(module) {

"use strict";
module.exports = React;

/***/ }),

/***/ "react-dom":
/*!***************************!*\
  !*** external "ReactDom" ***!
  \***************************/
/***/ (function(module) {

"use strict";
module.exports = ReactDom;

/***/ }),

/***/ "react-dom/client":
/*!*********************************!*\
  !*** external "ReactDomClient" ***!
  \*********************************/
/***/ (function(module) {

"use strict";
module.exports = ReactDomClient;

/***/ }),

/***/ "react-redux":
/*!*****************************!*\
  !*** external "ReactRedux" ***!
  \*****************************/
/***/ (function(module) {

"use strict";
module.exports = ReactRedux;

/***/ }),

/***/ "reactstrap":
/*!*****************************!*\
  !*** external "Reactstrap" ***!
  \*****************************/
/***/ (function(module) {

"use strict";
module.exports = Reactstrap;

/***/ }),

/***/ "redux":
/*!************************!*\
  !*** external "Redux" ***!
  \************************/
/***/ (function(module) {

"use strict";
module.exports = Redux;

/***/ }),

/***/ "containers/SudoMode/SudoMode":
/*!***************************!*\
  !*** external "SudoMode" ***!
  \***************************/
/***/ (function(module) {

"use strict";
module.exports = SudoMode;

/***/ }),

/***/ "classnames":
/*!*****************************!*\
  !*** external "classnames" ***!
  \*****************************/
/***/ (function(module) {

"use strict";
module.exports = classnames;

/***/ }),

/***/ "moment":
/*!*************************!*\
  !*** external "moment" ***!
  \*************************/
/***/ (function(module) {

"use strict";
module.exports = moment;

/***/ }),

/***/ "./client/lang/src/en.json":
/*!*********************************!*\
  !*** ./client/lang/src/en.json ***!
  \*********************************/
/***/ (function(module) {

"use strict";
module.exports = /*#__PURE__*/JSON.parse('{"MFABackupCodesRegister.COPY":"Copy codes","MFABackupCodesRegister.COPY_RECENT":"Copied!","MFABackupCodesRegister.DOWNLOAD":"Download","MFABackupCodesRegister.FINISH":"Finish","MFABackupCodesRegister.PRINT":"Print codes","MFABackupCodesVerify.DESCRIPTION":"Use one of the recovery codes you received","MFABackupCodesVerify.LABEL":"Enter recovery code","MFABackupCodesVerify.NEXT":"Next","MFALogin.SOMETHING_WENT_WRONG":"Something went wrong!","MFALogin.TRY_AGAIN":"Try again","MFAMethodTile.UNAVAILABLE":"Unsupported: ","MFARegister.BACK":"Back","MFARegister.HELP":"Find out more.","MFARegister.NEXT":"Next","MFARegister.RECOVERY_HELP":"How to use recovery codes.","MFARegister.REGISTER_WITH":"Register with {method}","MFARegister.SETUP_COMPLETE_CONTINUE":"Continue","MFARegister.SETUP_COMPLETE_DESCRIPTION":"You will be able to edit these settings later from your profile area.","MFARegister.TITLE":"Multi-factor authentication","MFASelectMethod.UNAVAILABLE":"unavailable","MFAVerify.BACK":"Back","MFAVerify.METHOD_UNAVAILABLE":"This authentication method is unavailable","MFAVerify.MORE_OPTIONS":"More options","MFAVerify.OTHER_METHODS_TITLE":"Try another way to verify","MFAVerify.TITLE":"Log in","MFAVerify.VERIFY_WITH":"Verify with {method}","MultiFactorAuthentication.ACCOUNT_RESET_ACTION":"Send account reset email","MultiFactorAuthentication.ACCOUNT_RESET_CONFIRMATION":"You are about to reset this account. Their password and multi-factor authentication will be reset. Continue?","MultiFactorAuthentication.ACCOUNT_RESET_CONFIRMATION_BUTTON":"Yes, send reset email","MultiFactorAuthentication.ACCOUNT_RESET_DESCRIPTION":"Ensure that the person requesting a reset is the real person associated with this account before proceeding. An email will be sent to the member\'s address with a link to reset both their password and multi-factor authentication methods.","MultiFactorAuthentication.ACCOUNT_RESET_SENDING":"Sending...","MultiFactorAuthentication.ACCOUNT_RESET_SENDING_FAILURE":"We were unable to send an email, please try again later.","MultiFactorAuthentication.ACCOUNT_RESET_SENDING_SUCCESS":"An email has been sent.","MultiFactorAuthentication.ACCOUNT_RESET_TITLE":"Help user reset account","MultiFactorAuthentication.ADD_ANOTHER_METHOD":"Add another MFA method","MultiFactorAuthentication.ADD_FIRST_METHOD":"Add an MFA method","MultiFactorAuthentication.ADMIN_SETUP_COMPLETE_CONTINUE":"Your settings have been updated","MultiFactorAuthentication.BACKUP_REGISTERED":"{method}: Created {date}","MultiFactorAuthentication.CONFIRMATION_TITLE":"Are you sure?","MultiFactorAuthentication.DEFAULT_CONFIRM_BUTTON":"Confirm","MultiFactorAuthentication.DEFAULT_CONFIRM_DISMISS_BUTTON":"Cancel","MultiFactorAuthentication.DEFAULT_REGISTERED":"{method} (default): Registered","MultiFactorAuthentication.DELETE_CONFIRMATION":"Are you sure you want to remove this method","MultiFactorAuthentication.DELETE_CONFIRMATION_BUTTON":"Remove method","MultiFactorAuthentication.EXTRA_LAYER_DESCRIPTION":"Every time you log into your account, you\'ll need your password and an additional form of verification.","MultiFactorAuthentication.EXTRA_LAYER_IMAGE_ALT":"Shields indicating additional protection","MultiFactorAuthentication.EXTRA_LAYER_TITLE":"Extra layer of protection","MultiFactorAuthentication.GET_STARTED":"Get started","MultiFactorAuthentication.HOW_IT_WORKS":"How it works","MultiFactorAuthentication.HOW_MFA_WORKS":"How multi-factor authentication works","MultiFactorAuthentication.MORE_OPTIONS_IMAGE_ALT":"Graphic depicting various MFA options","MultiFactorAuthentication.NO_METHODS_REGISTERED":"No MFA methods have been registered. Add one using the button below","MultiFactorAuthentication.NO_METHODS_REGISTERED_READONLY":"This member has not registered any MFA methods yet","MultiFactorAuthentication.REGISTERED":"{method}: Registered","MultiFactorAuthentication.REMOVE_METHOD":"Remove","MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION":"All existing codes will be made invalid and new codes will be created","MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION_BUTTON":"Reset codes","MultiFactorAuthentication.RESET_METHOD":"Reset","MultiFactorAuthentication.SELECT_METHOD":"Select a verification method","MultiFactorAuthentication.SETUP_COMPLETE_TITLE":"Multi-factor authentication is now set up","MultiFactorAuthentication.SETUP_LATER":"Setup later","MultiFactorAuthentication.SET_AS_DEFAULT":"Set as default method","MultiFactorAuthentication.TITLE":"Add extra security to your account","MultiFactorAuthentication.TRY_AGAIN_ERROR":"Something went wrong. Please try again.","MultiFactorAuthentication.UNIQUE_DESCRIPTION":"This verification is only available to you. Even if someone gets your password, they will not be able to access your account.","MultiFactorAuthentication.UNIQUE_IMAGE_ALT":"Person with tick indicating uniqueness","MultiFactorAuthentication.UNIQUE_TITLE":"Unique to you","MultiFactorAuthentication.UNKNOWN_ERROR":"An unknown error occurred."}');

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
!function() {
"use strict";
/*!******************************************!*\
  !*** ./client/src/bundles/bundle-cms.js ***!
  \******************************************/


__webpack_require__(/*! ../legacy */ "./client/src/legacy/index.js");
__webpack_require__(/*! ../boot/cms */ "./client/src/boot/cms/index.js");
}();
/******/ })()
;
//# sourceMappingURL=bundle-cms.js.map