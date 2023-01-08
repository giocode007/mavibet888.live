@extends('layouts.player')
@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
@section('content')
<div id="main">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-first">
                    <h3 class="text-white">Commission History of <span class="text-warning">({{ Str::upper($selectedUser->user_name) }})</span></h3>
                    <p class="text-subtitle text-muted">commission information list</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-last">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" onclick="history.back()">Agents</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Commissions</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        {{-- message --}}
        {!! Toastr::message() !!}
        <section class="section">
            <div class="card">
                @if ($selectedUser->role_type != 'Player')
                <div class="d-flex card-header justify-content-between">
                    @if (Auth::user()->role_type == 'Operator')
                        <a href="javascript:void(0)" id="convert" data-id="{{ $selectedUser->id }}">
                            <span class="p-3 badge bg-warning"><i class="icon-mid bi bi-plus-circle me-2"></i>CONVERT</span>
                        </a>
                    @endif
                </div>
                @endif
                <div class="card-body">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>Transaction</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Amount</th>
                                <th>Current Commission</th>
                                <th>Note</th>
                                <th>Date</th>
                            </tr>    
                        </thead>
                        <tbody id="event-crud">
                            @foreach ($commissionHistory as $transaction)
                            <tr id="commission_id_{{ $transaction['id'] }}">
                                <td class="name" style="text-transform:uppercase;">{{ $transaction['transaction_type'] }}</td>
                                <td class="name">{{ $transaction['from'] }}</td>
                                <td class="name">{{ $transaction['to'] }}</td>
                                @if (($transaction['transaction_type'] == 'commission get'
                                || $transaction['transaction_type'] == 'commission') && $transaction['status'] == 1)
                                <td class="name bg-success text-white">{{ $transaction['amount'] }}</td>
                                @else
                                <td class="name bg-danger text-white">{{ $transaction['amount'] }}</td>
                                @endif
                                <td class="name">{{ $transaction['current_commission'] }}</td>
                                <td class="name">{{ $transaction['note'] }}</td>
                                <td class="name">{{ $transaction['date'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

{{-- MODAL --}}
<div class="modal fade w-100" id="ajax-crud-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="fightModal"></h4>
        </div>
        <div class="modal-body">
            <form id="playerForm" name="playerForm" class="form-horizontal">
               <input type="hidden" name="playerId" id="playerId">
                <div class="form-group">
                    <label for="amount" class="col-sm-2 control-label">Enter Amount</label>
                    <div class="col-sm-12">
                        <input type="number" class="form-control" id="amount" name="amount" value="" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="note" class="col-sm-2 control-label">Leave a note</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" id="note" name="note" value="" required="">
                    </div>
                </div>

                <div class="col-sm-offset-2 col-sm-10">
                 <button type="submit" class="btn bg-warning text-white" id="btn-save">CONVERT
                 </button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            
        </div>
    </div>
    </div>
    </div>


@push('scripts')
<script>
    // Simple Datatable
    let table1 = document.querySelector('#table1');
    let dataTable = new simpleDatatables.DataTable(table1);
</script>

<script>
    $(document).ready(function () {
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      
      $('body').on('click', '#convert', function () {
        $('#fightModal').html("Convert");
        $('#ajax-crud-modal').modal('show');
        $('#playerId').val($(this).data('id'));
        $('#btn-save').val("convert");
        $('#btn-save').html("Convert");
     });

    });
   
    if ($("#playerForm").length > 0) {
        $("#playerForm").validate({
   
       submitHandler: function(form) {
        $('#btn-save').html('Converting...');

        var playerId = document.getElementById("playerId").value;
        var amount = document.getElementById("amount").value;
        var note = document.getElementById("note").value;
        var saveValue = document.getElementById("btn-save").value;

        $.ajax({
            url: "{{ route('agentDepositWithdraw') }}",
            type: "GET",
            data: {playerId:playerId,amount:amount,note:note,saveValue:saveValue},
            success: function (response) {
                if(response.success){
                    location.reload();
                }else{
                    $('#btn-save').html("Convert");
                    alert('INVALID INPUT!');
                }
            },
            error: function (response) {
                console.log('Error:', response);
            }
        });
      }
    })
  }
     
    
</script>
@endpush
@endsection
