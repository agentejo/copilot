<field-repeater>

    <div class="uk-alert" show="{ !items.length }">
        { App.i18n.get('No items') }.
    </div>

    <div name="itemscontainer" class="uk-sortable" show="{items.length}">
        <div class="uk-margin uk-panel-box uk-panel-card" each="{ item,idx in items }" data-idx="{idx}">

            <cp-field class="uk-width-1-1" field="{ parent.field }" options="{ opts.options }" bind="items[{ idx }].value"></cp-field>

            <div class="uk-panel-box-footer uk-bg-light">
                <a onclick="{ parent.remove }"><i class="uk-icon-trash-o"></i></a>
            </div>
        </div>
    </div>

    <div class="uk-margin">
        <a class="uk-button" onclick="{ add }"><i class="uk-icon-plus-circle"></i> { App.i18n.get('Add item') }</a>
    </div>

    <script>

        var $this = this;

        riot.util.bind(this);

        this.items = [];
        this.field = opts.field || {type:'text'};

        this.$initBind = function() {
            this.root.$value = this.items;
        };

        this.$updateValue = function(value) {

            if (!Array.isArray(value)) {
                value = [];
            }

            if (JSON.stringify(this.items) != JSON.stringify(value)) {
                this.items = value;
                this.update();
            }

        }.bind(this);

        this.on('bindingupdated', function() {
            this.$setValue(this.items);
        });

        this.on('mount', function() {

            UIkit.sortable(this.itemscontainer, {

                animation: false,
                handleClass: 'uk-panel-box-footer'

            }).element.on("change.uk.sortable", function(e, sortable, ele) {

                ele = App.$(ele);

                var items  = $this.items,
                    cidx   = ele.index(),
                    oidx   = ele.data('idx');

                items.splice(cidx, 0, items.splice(oidx, 1)[0]);

                $this.items = [];
                $this.update();

                setTimeout(function() {
                    $this.items = items;
                    $this.$setValue(items);
                    $this.update();
                }, 10);

            });

        });

        add() {
            this.items.push({type:this.field.type, value:''});
        }

        remove(e) {
            this.items.splice(e.item.idx, 1);
        }

    </script>

</field-repeater>
