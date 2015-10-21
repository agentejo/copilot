
<ul  class="uk-breadcrumb">
    @render('coco:views/partials/subnav.php')
    <li><span class="uk-text-primary">@lang('Update')</span></li>
</ul>


<div riot-view>

    <div class="uk-panel uk-panel-space uk-panel-box uk-panel-card uk-text-center">

        <h1>@lang('System Update')</h1>

        <div class="uk-text-large uk-margin-large-top" show="{status.length}">

            <p class="{idx<parent.status.length-1 ? 'uk-text-muted':'uk-text-primary'}" each="{s,idx in status}">{ s }</p>

            <p class="uk-text-muted">
                <i class="uk-icon-spinner uk-icon-spin"></i>
            </p>

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

            $this.status.push('Generating a backup...');

            App.request('/coco/update/update/0', {nc:Math.random()}).then(function(){

                $this.status.push('Downloading latest version...');
                $this.update();

                App.request('/coco/update/update/1', {nc:Math.random()}).then(function(){

                    $this.status.push('Extracting zip file...');
                    $this.update();

                    App.request('/coco/update/update/2', {nc:Math.random()}).then(function(){

                        $this.status.push('Swapping files...');
                        $this.update();

                        App.request('/coco/update/update/3', {nc:Math.random()}).then(function(){

                            $this.status.push('Cleanup...');
                            $this.update();

                            App.request('/coco/update/update/4', {nc:Math.random()}).then(function(){
                                location.href = App.route('/coco');
                            });
                        });
                    });
                });
            });
        };

    </script>

</div>
