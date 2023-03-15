@extends('layouts.master')
@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
@section('menu')
@extends('sidebar.summary_logs')
@endsection
@section('content')
<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-first">
                    <h3 class="text-white">Summary Reports</h3>
                    <p class="text-subtitle text-muted"></p>
                </div>
            </div>
        </div>
        {{-- message --}}
        {!! Toastr::message() !!}
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Event</th>
                                <th>Total Commission</th>
                            </tr>    
                        </thead>
                        <tbody id="event-crud">
                            @foreach ($eventCommission as $transaction)
                            <tr id="commission_id_{{ $transaction['id'] }}">
                                <td class="name" style="text-transform:uppercase;">{{ $transaction['date'] }}</td>
                                <td class="name">{{ $transaction['event_name'] }}</td>
                                <td class="name">{{ $transaction['total_commission'] }}</td>
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