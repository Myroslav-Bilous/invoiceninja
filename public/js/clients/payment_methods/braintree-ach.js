/*! For license information please see braintree-ach.js.LICENSE.txt */
!function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/",n(n.s=24)}({24:function(e,t,n){e.exports=n("cGea")},cGea:function(e,t){var n;window.braintree.client.create({authorization:null===(n=document.querySelector('meta[name="client-token"]'))||void 0===n?void 0:n.content}).then((function(e){return braintree.usBankAccount.create({client:e})})).then((function(e){var t;null===(t=document.getElementById("authorize-bank-account"))||void 0===t||t.addEventListener("click",(function(t){t.target.parentElement.disabled=!0,document.getElementById("errors").hidden=!0,document.getElementById("errors").textContent="";var n={accountNumber:document.getElementById("account-number").value,routingNumber:document.getElementById("routing-number").value,accountType:document.querySelector('input[name="account-type"]:checked').value,ownershipType:document.querySelector('input[name="ownership-type"]:checked').value,billingAddress:{streetAddress:document.getElementById("billing-street-address").value,extendedAddress:document.getElementById("billing-extended-address").value,locality:document.getElementById("billing-locality").value,region:document.getElementById("billing-region").value,postalCode:document.getElementById("billing-postal-code").value}};if("personal"===n.ownershipType){var r=document.getElementById("account-holder-name").value.split(" ",2);n.firstName=r[0],n.lastName=r[1]}else n.businessName=document.getElementById("account-holder-name").value;e.tokenize({bankDetails:n,mandateText:'By clicking ["Checkout"], I authorize Braintree, a service of PayPal, on behalf of [your business name here] (i) to verify my bank account information using bank information and consumer reports and (ii) to debit my bank account.'}).then((function(e){document.querySelector("input[name=nonce]").value=e.nonce,document.getElementById("server_response").submit()})).catch((function(e){t.target.parentElement.disabled=!1,document.getElementById("errors").textContent="".concat(e.details.originalError.message," ").concat(e.details.originalError.details.originalError[0].message),document.getElementById("errors").hidden=!1}))}))})).catch((function(e){document.getElementById("errors").textContent="".concat(error.details.originalError.message," ").concat(error.details.originalError.details.originalError[0].message),document.getElementById("errors").hidden=!1}))}});