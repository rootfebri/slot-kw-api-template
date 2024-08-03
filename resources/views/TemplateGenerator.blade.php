<div>
    @if(session('error'))
        {{session('error')}}
    @endif
</div>
<form method="POST">
    @csrf
    <label>
        <h1>Tunnel</h1>
        <input type="text" name="tunnel" value="{{ request()->debug? 'asd.asda.asd':'' }}" required placeholder="azure.blob.core.windows.net"/>
    </label>
    <label>
        <h1>Kontainer</h1>
        <input type="hidden" name="debug" value="{{request()->debug}}"/>
        <input type="text" name="kontainer" placeholder="required for azure, eg: $web"/>
    </label>
    <button type="submit">Generate</button>
</form>
