<form-donation
    submit-route="{{ $submitUrl }}"
    :amount-options="{{ Illuminate\Support\Js::from($amountOptions) }}"
    :currencies="{{ Illuminate\Support\Js::from($currencyOptions) }}"
    :messages="{{ Illuminate\Support\Js::from($errors->donation->all()) }}"
    :list-minimal-payments="{{ Illuminate\Support\Js::from($listMinimalPayments) }}"
></form-donation>
