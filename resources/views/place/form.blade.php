@include('status')

<div class="form-row">
    <div class="form-group col-md-3">
        <label for="id">ID</label>
        <input id="id" name="id" type="text" class="form-control @error('id') is-invalid @enderror" placeholder="Leave empty to auto-generate an ID" value="{{old('id', $place->id)}}">
        @error('id')
        <div class="form-text text-danger is-invalid">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-md-9">
        <label for="name">Location name</label>
        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Enter the location name" value="{{old('name', $place->name)}}">
        @error('name')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </div>
</div>
