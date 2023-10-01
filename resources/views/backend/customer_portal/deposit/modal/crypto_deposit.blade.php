<form method="post" class="validate" autocomplete="off" action="{{ route('deposit.automatic_deposit',$deposit_method->id) }}" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="row p-2">
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Credit Account') }}</label>
                <select class="form-control auto-select" data-selected="{{ old('credit_account') }}" name="credit_account" id="credit_account" required>
                    <option value="">{{ _lang('Select One') }}</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" data-currency="{{ $account->savings_type->currency->name }}">{{ $account->account_number }} ({{ $account->savings_type->name }} - {{ $account->savings_type->currency->name }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Amount') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="account-currency">{{ get_base_currency() }}</span>
                    </div>
                    <input type="text" class="form-control float-field" name="amount" id="amount" value="{{ old('amount') }}" required>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Base Amount') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="gateway-currency">{{ get_base_currency() }}</span>
                    </div>
                    <input type="text" class="form-control float-field" name="converted_amount" id="converted_amount" value="{{ old('converted_amount') }}" readonly="true" required>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Total Amount') }} ({{ _lang('Charge Included') }})</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">{{ get_base_currency() }}</span>
                    </div>
                    <input type="text" class="form-control float-field" name="total_amount" id="total_amount" value="{{ old('total_amount') }}" readonly>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Minimum Deposit') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">{{ get_base_currency() }}</span>
                    </div>
                    <input type="text" class="form-control float-field" value="{{ $deposit_method->minimum_amount }}" readonly>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Maximum Deposit') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">{{ get_base_currency() }}</span>
                    </div>
                    <input type="text" class="form-control float-field" value="{{ $deposit_method->maximum_amount }}" readonly>
                </div>
            </div>
        </div>

        <div class="col-md-12 mb-2">
            <h6 class="text-danger text-center"><b>{{ decimalPlace($deposit_method->fixed_charge, currency()) }} + {{ $deposit_method->charge_in_percentage }}% {{ _lang('transaction charge will be applied') }}</b></h6>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <button type="submit" class="btn btn-primary  btn-block" id="submit-btn"><i class="ti-check-box"></i>&nbsp;{{ _lang('Process') }}</button>
            </div>
        </div>
    </div>
</form>

<script>
(function ($) {
  "use strict";

    $('#credit_account').val() != '' ? $("#account-currency").html($('#credit_account').find(':selected').data('currency')) : '';

    $(document).on('change','#credit_account', function(){
		$("#account-currency").html($(this).find(':selected').data('currency'));
        $("#amount").keyup();
	});

    $(document).on('keyup','#amount', function(){
        var from = $("#account-currency").html();
	  	var to = $("#gateway-currency").html();

	  	var amount = $(this).val();

		if($("#credit_account").val() == ''){
			Swal.fire(
				'{{ _lang('Alert') }}',
				'{{ _lang('Please select credit account first !') }}',
				'warning'
			);
			$(this).val('');
			return;
		}

		if(amount != ''){
            $.ajax({
				url: '{{ route('transfer.get_exchange_amount') }}/' + from + '/' + to + '/' + amount,
				beforeSend: function(){
					$("#submit-btn").prop('disabled', true);
				},success: function(data){
                    var decimalPlace = {{ $deposit_method->is_crypto == 1 ? 6 : 2 }};

					var json = JSON.parse(JSON.stringify(data));
                    var converted_amount = parseFloat(json['amount']).toFixed(decimalPlace);
                    $("#converted_amount").val(converted_amount);
                    
                    //Calculate Total Amount
                    var exchangeRate = parseFloat({{ $deposit_method->exchange_rate }});
                    var gatewayAmount = (converted_amount / 1) * exchangeRate;
                    var fixedCharge = parseFloat({{ $deposit_method->fixed_charge }});
                    var chargeInPercentage = parseFloat({{ $deposit_method->charge_in_percentage > 0 ? $deposit_method->charge_in_percentage : 0 }});

                    var totalAmount = gatewayAmount + ((chargeInPercentage / 100) * gatewayAmount) + fixedCharge;

                    $("#total_amount").val(totalAmount.toFixed(decimalPlace));
					$("#submit-btn").prop('disabled', false);
				}
			});
		}else{
			$("#converted_amount").val('');
			$("#total_amount").val('');
		}
  });

})(jQuery);
</script>