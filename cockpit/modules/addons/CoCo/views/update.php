
<ul  class="uk-breadcrumb">
    @render('coco:views/partials/subnav.php')
    <li><span class="uk-text-primary">@lang('Update')</span></li>
</ul>


<div riot-view>

    <div class="uk-panel uk-panel-space uk-panel-box uk-panel-card uk-text-center">

        <h1>@lang('System Update')</h1>

        <div show="{status.length}">

            <p class="uk-text-muted uk-text-large">
                <i class="uk-icon-spinner uk-icon-spin"></i>
            </p>

            <p class="{idx===0 ? 'uk-text-large uk-text-primary':'uk-text-success'}" each="{s,idx in status}">{ s }</p>

        </div>

        <div show="{!status.length}">
            <p class="uk-text-muted uk-text-large">
                @lang('This is really experimental right now').
            </p>

            <a class="uk-button uk-button-large uk-button-primary" onclick="{ start }">@lang('Update')</a>
        </div>

    </div>

    <script type="view/script">

        var $this = this;

        this.status = [];

        start() {

            $this.status.unshift('Generating a backup');

            App.request('/coco/update/update/0', {nc:Math.random()}).then(function(data){

                if (data && data.error) {
                    return App.ui.alert(data.message || 'Uuups, something went wrong!');
                }

                $this.status.unshift('Downloading latest version');
                $this.update();

                App.request('/coco/update/update/1', {nc:Math.random()}).then(function(data){

                    if (data && data.error) {
                        return App.ui.alert(data.message || 'Uuups, something went wrong!');
                    }

                    $this.status.unshift('Extracting zip file');
                    $this.update();

                    App.request('/coco/update/update/2', {nc:Math.random()}).then(function(data){

                        if (data && data.error) {
                            return App.ui.alert(data.message || 'Uuups, something went wrong!');
                        }

                        $this.status.unshift('Swapping files');
                        $this.update();

                        App.request('/coco/update/update/3', {nc:Math.random()}).then(function(data){

                            if (data && data.error) {
                                return App.ui.alert(data.message || 'Uuups, something went wrong!');
                            }

                            $this.status.unshift('Cleaning up');
                            $this.update();

                            App.request('/coco/update/update/4', {nc:Math.random()}).then(function(data){
                                location.href = App.route('/coco');
                            });
                        });
                    });
                });
            });
        };

    </script>

</div>
