/**
 * http://codepen.io/Mobilpadde/pen/QjMYeL
 */
(function(){

    var Stars = function(c, color) {

        var ctx = c.getContext("2d");

        var background = function(){
            var grdnt = ctx.createLinearGradient(0, 0, 0, c.height);
            //grdnt.addColorStop(0, "#000");
            //grdnt.addColorStop(.5, "#135288");
            //grdnt.addColorStop(1, "#0C5663");
            //grdnt.addColorStop(1, "#039be5");
            grdnt.addColorStop(1, color);
            ctx.fillStyle = grdnt;
            ctx.fillRect(0, 0, c.width, c.height);
        };

        var num = (Math.min(window.innerWidth, window.innerHeight) / Math.max(c.width, c.height)) * 750,
            makeStar = function(){
                return {
                    radius: Math.random() * (3 - .5) + .5,
                    pos: {
                        x: Math.random() * c.width,
                        y: Math.random() * c.height
                    },
                    moveTo: {
                        x: Math.random() * c.width,
                        y: Math.random() * c.height
                    },
                    bigger: ~~(Math.random() * 2),
                    speed: Math.random() * (c.width / c.height / 40)
                }
            },
            stars = [],
            draw = function(star){
                ctx.fillStyle = "#fff";
                ctx.beginPath();
                ctx.arc(star.pos.x, star.pos.y, star.radius, 0, Math.PI * 2);
                ctx.fill();
                if(star.bigger){
                    star.radius += .01;
                    if(star.radius >= 3) star.bigger = false;
                }else{
                    star.radius -= .01;
                    if(star.radius <= .3) star.bigger = true;
                }
                if(
                    star.moveTo.x >= star.pos.x - 5 &&
                    star.moveTo.x <= star.pos.x + 5
                ){
                    star.moveTo.x = Math.random() * c.width;
                }
                else if(star.moveTo.x < star.pos.x) star.pos.x -= star.speed;
                else if(star.moveTo.x > star.pos.x) star.pos.x += star.speed;
                if(
                    star.moveTo.y >= star.pos.y - 5 &&
                    star.moveTo.y <= star.pos.y + 5
                ){
                    star.moveTo.y = Math.random() * c.height;
                }
                else if(star.moveTo.y < star.pos.y) star.pos.y -= star.speed;
                else if(star.moveTo.y > star.pos.y) star.pos.y += star.speed;

                for(var i = 0; i < stars.length; i++){
                    if(
                        star != stars[i] &&
                        Math.sqrt(
                            (star.pos.x - stars[i].pos.x) * (star.pos.x - stars[i].pos.x) +
                            (star.pos.y - stars[i].pos.y) * (star.pos.y - stars[i].pos.y)
                        ) < 50
                    ){
                        ctx.beginPath();
                        ctx.moveTo(star.pos.x, star.pos.y);
                        ctx.lineTo(stars[i].pos.x, stars[i].pos.y);
                        ctx.closePath();
                        ctx.strokeStyle = "#fff";
                        ctx.lineWidth = .025;
                        ctx.stroke();
                    }
                }
            }
        return {
            init: function(){
                for(var i = 0; i < num; i++){
                    stars.push(new makeStar());
                }

                background();
            },
            move: function(){
                setInterval(function(){
                    ctx.clearRect(0, 0, c.width, c.height);
                    background();
                    for(var i = 0; i < stars.length; i++){
                        draw(stars[i]);
                    }
                }, 1);
            }
        }
    };



    window.SKY = function(ele, color) {

        if (!ele) return;

        color = color || '#222';

        var canvas = $('<canvas class="uk-position-absolute uk-position-top uk-display-block"></canvas>').prependTo(ele);

        var c = canvas[0];

        c.width  = $(ele).width();
        c.height = $(ele).height();

        var sky = new Stars(c, color),
            ctx = c.getContext("2d");

        sky.init(c);
        sky.move();
        window.addEventListener("resize", function(){
            c.width = window.innerWidth;
            c.height = window.innerHeight;
        }, true);

        return canvas;
    };

    SKY(document.body);


}());
