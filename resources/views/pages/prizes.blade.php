@extends($layout)

@section('content')
    @if(App\Event::where('show_prize', true)->count() > 0 && App\Prize::all()->count() > 0)
        @foreach($events as $event)
            @if($event->show_prize && $event->hasPrizes())
                @include('prizes.partials.prize_details', ['event' => $event])
            @endif  
        @endforeach
    @else
        <div class="row">
            <div class="col s12">
                <p class="center-align"><i class="fa fa-smile-o" style="font-size: 200px;margin-top:60px;"></i></p>
                <h4 class="center-align">
                    We will be posting the results soon!
                </h4>
            </div>
        </div>
    @endif
@endsection