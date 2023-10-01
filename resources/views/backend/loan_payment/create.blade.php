@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-8 offset-lg-2">
		<div class="card">
			<div class="card-header text-center panel-title">
				{{ _lang('Add Loan Repayment') }}
			</div>
			<div class="card-body">
				<form method="post" class="validate" autocomplete="off" action="{{ route('loan_payments.store') }}" enctype="multipart/form-data">
					{{ csrf_field() }}
					<div class="row">

						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Payment Date') }}</label>						
								<input type="text" class="form-control datepicker" name="paid_at" id="paid_at" value="{{ old('paid_at') }}" required>
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Loan ID') }}</label>						
								<select class="form-control auto-select select2" data-selected="{{ old('loan_id') }}" id="loan_id" name="loan_id" required>
									<option value="">{{ _lang('Select One') }}</option>
									@foreach(\App\Models\Loan::with('currency')->where('status',1)->get() as $loan)
										<option value="{{ $loan->id }}" data-user-id="{{ $loan->borrower_id }}" data-currency="{{ $loan->currency->name }}" data-total-due="{{ ($loan->total_payable - $loan->total_paid) }}">{{ $loan->loan_id }} ({{ _lang('Total Due').' '.decimalPlace($loan->total_payable - $loan->total_paid, currency($loan->currency->name)) }})</option>
									@endforeach
								</select>
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Due Amount Date') }}</label>						
								<select class="form-control" name="due_amount_of" id="due_amount_of" required>
								</select>
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Late Penalties').' ( '._lang('It will apply if payment date is over') }} )</label>						
								<div class="input-group">
									<input type="text" class="form-control float-field" name="late_penalties" id="late_penalties" value="{{ old('late_penalties',0) }}">
									<div class="input-group-append">
										<span class="input-group-text currency"></span>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Repayment Amount') }}</label>						
								<div class="input-group">
									<input type="text" class="form-control float-field" name="repayment_amount" id="repayment_amount" value="{{ old('repayment_amount') }}" readonly="true" required>
									<div class="input-group-append">
										<span class="input-group-text currency"></span>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Total Amount') }}</label>						
								<div class="input-group">
									<input type="text" class="form-control float-field" name="total_amount" id="total_amount" value="{{ old('total_amount') }}" readonly="true" required>
									<div class="input-group-append">
										<span class="input-group-text currency"></span>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Remarks') }}</label>						
								<textarea class="form-control" name="remarks">{{ old('remarks') }}</textarea>
							</div>
						</div>
				
						<div class="col-md-12">
							<div class="form-group">
								<button type="submit" class="btn btn-primary btn-block">{{ _lang('Submit') }}</button>
							</div>
						</div>
					</div>			
				</form>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js-script')
<script>
	$(function() {

	"use strict";

	$(document).on('change','#loan_id',function(){

		var user_id = $(this).find(':selected').data('user-id');
		var currency = $(this).find(':selected').data('currency');
		var loan_id = $(this).val();

		if( loan_id != '' ){
			$.ajax({
				url: "{{ url('admin/loan_payments/get_repayment_by_loan_id') }}/" + loan_id,
				beforeSend: function(){
					$("#preloader").css("display","block"); 
				},success: function(data){
					$("#preloader").css("display","none");
					var json = JSON.parse(data);
					$("#due_amount_of").find('option').remove();
					$("#due_amount_of").append("<option value=''>{{ _lang('Select One') }}</option>");

					jQuery.each( json, function( i, val ) {
						$("#due_amount_of").append("<option value='" + val.id + "' data-penalty='" + val.penalty + "' data-amount='" + val.amount_to_pay + "'>" + val.repayment_date + "</option>");
					});

					//$("#due_amount_of").append("<option value='all' data-penalty='' data-amount=''>{{ _lang('All Due Amount') }}</option>");
				}
			});

			$(".currency").html(currency);
		}
	});

	$(document).on('change','#due_amount_of',function(){
		if($("#paid_at").val() == ''){
			alert("Please Select Payment date first");
			$(this).val('');
			return;
		}

		/*if($(this).val() == 'all'){
			var totalDue = $("#loan_id").find(':selected').data('total-due');
			$("#repayment_amount").val(totalDue);
			$("#total_amount").val(totalDue);
			return;
		}*/

		var repayment_date = $(this).find(':selected').text();
		var penalty = $(this).find(':selected').data('penalty');
		var repayment_amount = $(this).find(':selected').data('amount');

		var due = moment($("#paid_at").val()).diff( moment(repayment_date), 'days');

		$("#repayment_amount").val(repayment_amount);

		if(due > 0){
			$("#late_penalties").val(penalty);
			$("#total_amount").val(parseFloat(repayment_amount) + parseFloat(penalty));
		}else{
			$("#late_penalties").val(0);
			$("#total_amount").val(repayment_amount);
		}	
		
	});

	$(document).on('keyup','#late_penalties',function(){
		var penalty = $('#late_penalties').val();
		var repayment_amount = $('#repayment_amount').val();

		if(penalty == ''){
			$("#total_amount").val(parseFloat(repayment_amount));
		}else{
			$("#total_amount").val(parseFloat(repayment_amount) + parseFloat(penalty));
		}
		
	});
});
</script>
@endsection


