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
                    <h3 class="text-white">All Events</h3>
                    <p class="text-subtitle text-muted">events information list</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-last">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            @if (Auth::user()->role_type == 'Operator')
                                <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
                            @elseif (Auth::user()->role_type == 'Declarator')
                                <li class="breadcrumb-item"><a href="{{ route('declarator') }}">Home</a></li>
                            @endif
                            <li class="breadcrumb-item active" aria-current="page">View</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        {{-- message --}}
        {!! Toastr::message() !!}
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <a href="javascript:void(0)" id="create-new-post">
                        <span class="p-3 badge bg-success"><i class="icon-mid bi bi-plus-circle me-2"></i>ADD EVENT</span>
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Date Of Event</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th class="text-center">Modify</th>
                            </tr>    
                        </thead>
                        <tbody id="event-crud">
                            @foreach ($events as $event)
                                <tr id="event_id_{{ $event->id }}">
                                    <td class="name">{{ $event->event_name }}</td>
                                    <td class="name">{{ $event->fight_date_time }}</td>
                                    <td class="name">{{ $event->location }}</td>
                                    @if ($event->status == 0)
                                    <td class="email bg-secondary text-center font-bold text-white">Pending</td>
                                    @elseif ($event->status == 1)
                                    <td class="email bg-warning text-center font-bold"><a href="{{ url('arena/'.$event->id) }}" class="text-white">OnGoing</a></td>
                                    @else
                                    <td class="email bg-success text-center font-bold text-white">Completed</td>
                                    @endif
                                    <td class="text-center">
                                        <a href="javascript:void(0)" id="edit-event" data-id="{{ $event->id }}">
                                            <span class="badge bg-info p-2">EDIT</span>
                                        </a>
                                        <a href="{{ url('events/'.$event->id) }}" id="show-event" data-id="{{ $event->id }}">
                                            <span class="badge bg-danger p-2">GAMES</span>
                                        </a>
                                        @if ($event->status == 0)
                                        <a href="javascript:void(0)">
                                            <span class="badge bg-light-secondary p-2">BETS</span>
                                        </a>
                                        @elseif ($event->status == 1)
                                        <a href="{{ url('fights/'.$event->id) }}">
                                            <span class="badge bg-success p-2">BETS</span>
                                        </a>
                                        @else
                                        <a href="javascript:void(0)">
                                            <span class="badge bg-light-secondary p-2">BETS</span>
                                        </a>
                                        @endif
                                            
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
            <h4 class="modal-title" id="eventModal"></h4>
        </div>
        <div class="modal-body">
            <form id="eventForm" name="eventForm" class="form-horizontal">
               <input type="hidden" name="event_id" id="event_id">
               <input type="hidden" name="action_type" id="action_type">
                <div class="form-group">
                    <label for="event_name" class="col-sm-2 control-label">EVENT NAME</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" id="event_name" name="event_name" value="" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="fight_date_time" class="col-sm-2 control-label">DATE OF FIGHT</label>
                    <div class="col-sm-12">
                        <input type="datetime-local" class="form-control" id="fight_date_time" name="fight_date_time" value="" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="location" class="col-sm-2 control-label">LOCATION</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" id="location" name="location" value="" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="video_code" class="col-sm-2 control-label">VIDEO CODE</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" id="video_code" name="video_code" value="" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status" class="col-sm-4 control-label">EVENT STATUS</label>
                    <select class="form-control" id="status" name="status" value="">
                        <option value="0">Pending</option>
                        <option value="1">OnGoing</option>
                        <option value="2">Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label">PALASADA</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" id="palasada" name="palasada" value="0.10" required="">
                    </div>
                </div>

                <div id="fightNumber" class="form-group">
                    <label for="name" class="col-sm-2 control-label">START FROM FIGHT #</label>
                    <div class="col-sm-12">
                        <input type="number" class="form-control" id="fight_number" name="fight_number" value="1" required="">
                    </div>
                </div>

                <div class="col-sm-offset-2 col-sm-10">
                 <button type="submit" class="btn btn-primary" id="btn-save" value="create">Save
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
      $('#create-new-post').click(function () {
        document.getElementById('fightNumber').classList.remove('hide');
          $('#btn-save').val("create-post");
          $('#action_type').val("create");
          $('#eventForm').trigger("reset");
          $('#eventModal').html("Add new event");
          $('#ajax-crud-modal').modal('show');
      });
   
      $('body').on('click', '#edit-event', function () {
        var event_id = $(this).data('id');

        $.get('events/'+event_id+'/edit', function (data) {
            document.getElementById('fightNumber').classList.add('hide');
           $('#eventModal').html("Edit Event");
            $('#btn-save').val("edit-event");
            $('#action_type').val("edit");
            $('#ajax-crud-modal').modal('show');
            $('#event_id').val(data.id);
            $('#event_name').val(data.event_name);
            $('#fight_date_time').val(data.fight_date_time);
            $('#location').val(data.location);
            $('#video_code').val(data.video_code);  
            $('#status').val(data.status);  
            $('#palasada').val(data.palasada);  
        })
     });

     

      $('body').on('click', '.delete-post', function () {
          var event_id = $(this).data("id");
          confirm("Are You sure want to delete !");
   
          $.ajax({
              type: "DELETE",
              url: "{{ url('events')}}"+'/'+event_id,
              success: function (data) {
                  $("#event_id_" + event_id).remove();
              },
              error: function (data) {
                  console.log('Error:', data);
              }
          });
      });   
    });
   
   if ($("#eventForm").length > 0) {
        $("#eventForm").validate({
   
       submitHandler: function(form) {
        var actionType = $('#btn-save').val();
        $('#btn-save').html('Sending..');
        
        $.ajax({
            url: "{{ route('events.store') }}",
            type: "POST",
            data: $('#eventForm').serialize(),
            dataType: 'json',
            success: function (data) {
                window.location = "/events";
            },
            error: function (data) {
                console.log('Error:', data);
                $('#btn-save').html('ERROR');
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
