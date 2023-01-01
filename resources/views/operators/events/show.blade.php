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
                    <h3 class="text-white"><a href="{{ url('arena/'.$selectedEvent[0]->id) }}">{{ \Carbon\Carbon::parse($selectedEvent[0]->fight_date_time)->isoFormat('dddd') }} {{ $selectedEvent[0]->event_name }} - {{ \Carbon\Carbon::parse($selectedEvent[0]->fight_date_time)->isoFormat('MM/DD/Y') }}</a></h3>
                    <p class="text-subtitle text-muted">fights information list</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-last">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="/events">Events</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Fights</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        {{-- message --}}
        {!! Toastr::message() !!}
        <section class="section">
            <div class="card">
                {{-- <div class="card-header">
                    <a href="javascript:void(0)" id="create-new-post">
                        <span class="p-3 badge bg-success"><i class="icon-mid bi bi-plus-circle me-2"></i>ADD EVENT</span>
                    </a>
                </div> --}}
                <div class="card-body">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>Fight Number</th>
                                <th>Result</th>
                                <th>Meron Payout</th>
                                <th>Wala Payout</th>
                                <th>IsOpen</th>
                                <th>Declared By</th>
                                <th class="text-center">Modify</th>
                            </tr>    
                        </thead>
                        <tbody id="event-crud">
                            @foreach ($fights as $fight)
                                <tr id="fight_id_{{ $fight->id }}">
                                    <td class="name text-center">{{ $fight->fight_number }}</td>
                                    @if ($fight->result == 'meron')
                                        <td class="name font-bold text-danger" style="text-transform:uppercase;">{{ $fight->result }}</td>
                                    @elseif ($fight->result == 'wala')
                                        <td class="name font-bold text-primary" style="text-transform:uppercase;">{{ $fight->result }}</td>
                                    @elseif ($fight->result == 'draw')
                                        <td class="name font-bold text-success" style="text-transform:uppercase;">{{ $fight->result }}</td>
                                    @elseif ($fight->result == 'cancel')
                                        <td class="name font-bold text-light-secondary" style="text-transform:uppercase;">{{ $fight->result }}</td>
                                    @else
                                    <td class="name font-bold" style="text-transform:uppercase;">N/A</td>
                                    @endif
                                    <td class="name">{{ $fight->payoutMeron }}</td>
                                    <td class="name">{{ $fight->payoutWala }}</td>
                                    @if ($fight->isOpen == 0)
                                    <td class="bg-danger text-center font-bold text-white">CLOSE</td>
                                    @else
                                    <td class="bg-success text-center font-bold text-white">OPEN</td>
                                    @endif

                                    <td class="name text-center">{{ ($fight->declared_by != null ) ? $fight->declared_by : 'N/A' }}</td>
                                    
                                    <td class="text-center">
                                        @if ($fight->result != null)
                                            <a href="javascript:void(0)" id="edit-fight" data-id="{{ $fight->id }}">
                                                <span class="badge bg-warning p-2">Correction</span>
                                            </a>
                                        @else
                                            <a href="javascript:void(0)">
                                                <span class="badge bg-light-secondary p-2">Correction</span>
                                            </a>
                                        @endif

                                        <a href="{{ url('fightsbet/'.$fight->id) }}" id="show-fight" data-id="{{ $fight->id }}">
                                            <span class="badge bg-success p-2">Bets</span>
                                        </a>
                                        
                                        {{-- <a href="javascript:void(0)" id="delete-post" data-id="{{ $event->id }}"><span class="badge bg-danger"><i class="bi bi-trash"></i></span></a> --}}
                                    </td>
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
            <form id="fightForm" name="fightForm" class="form-horizontal">
               <input type="hidden" name="fightId" id="fightId">
                <div class="form-group">
                    <label for="fight_name" class="col-sm-2 control-label">FIGHT NUMBER</label>
                    <div class="col-sm-12">
                        <input disabled="disabled" class="form-control" id="fight_name" name="fight_name" value="" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="result" class="col-sm-4 control-label">FIGHT RESULT</label>
                    <select class="form-control" id="result" name="result" value="">
                        <option value="meron">MERON</option>
                        <option value="wala">WALA</option>
                        <option value="draw">DRAW</option>
                        <option value="cancel">CANCEL</option>
                    </select>
                </div>

                <div class="col-sm-offset-2 col-sm-10">
                 <button type="submit" class="btn btn-warning" id="btn-save">Reverse
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
   
      $('body').on('click', '#edit-fight', function () {
        var fightId = $(this).data('id');

        $.ajax({
            url: "{{ route('getFight') }}",
            type: "GET",
            data: {fightId:fightId},
            dataType: 'json',
            success: function (response) {
                $('#fightModal').html("Edit Fight");
                $('#ajax-crud-modal').modal('show');
                $('#fightId').val(response[0].id);
                $('#fight_name').val(response[0].fight_number);
                $('#result').val(response[0].result);  
            },
            error: function (response) {
                console.log('Error:', data);
            }
        });

        
     });

    
       
    });
   
   if ($("#fightForm").length > 0) {
        $("#fightForm").validate({
   
       submitHandler: function(form) {
        $('#btn-save').html('Reversing...');
        var fightId = document.getElementById("fightId").value;
        var result = document.getElementById("result").value;
        
        $.ajax({
            url: "{{ route('reverseFight') }}",
            type: "GET",
            data: {fightId:fightId,result:result},
            success: function (response) {
                location.reload();
                
                // $('#btn-save').html('Reverse');
                // $('#ajax-crud-modal').modal('hide');
                // toastr.options.timeOut = 1500; // 1.5s 

                // if(response.success){
                //     toastr.success('Reserve payment successful!');
                // }else{
                //     toastr.error('Reserve payment error');
                // }
            },
            error: function (response) {
                console.log('Error:', response);
            }
        });
      }
    })
  }
     
    
  </script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
       config = {
          enableTime: true,
          dateFormat: 'Y-m-d H:i',
      }
      flatpickr("input[type=datetime-local]", config);
  </script>
@endpush
@endsection
