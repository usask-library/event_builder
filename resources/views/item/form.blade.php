@include('status')

<div class="form-row">
    <div class="form-group col-md-3">
        <label for="id">ID</label>
        <input id="id" name="id" type="text" class="form-control @error('id') is-invalid @enderror" placeholder="ID" value="{{old('id', $item->id)}}">
        @error('id')
        <div class="form-text text-danger is-invalid">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="identifier">Identifier</label>
        <input id="identifier" name="identifier" type="text" class="form-control @error('identifier') is-invalid @enderror" placeholder="Enter identifier" value="{{old('identifier', $item->identifier)}}">
        @error('identifier')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="item_id">Object</label>
        <select id="item_id" name="item_id" class="form-control selectpicker show-tick @error('roles') is-invalid @enderror">
            <option>-- None --</option>
            @foreach($objects as $object)
                <option value="{{ $object->id }}"
                    @if(old('item_id'))
                        {{ (old('item_id') == $object->id) ? 'selected' : '' }}
                    @else
                        {{ ($object->id == $item->item_id) ? 'selected' : '' }}
                    @endif
                >{{ $object->name }}</option>
            @endforeach
        </select>
        @error('item_id')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </div>
</div>
