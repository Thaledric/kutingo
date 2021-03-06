/**********************
        Score
        
        Score and interaction with it.
***********************************/

function Score(app, parentstate){
    var self = this;
    
    // Members Vars ///////////////////////////////////////////////////////////////

    self.app;
    self.parentstate = parentstate;
    
    // Logic
    
    self.timescore;
    self.bonusscore;
    self.totalscore;
    
    // display
    
    self.canvas;
    self.context;
    self.sprite;
    self.material;
    
    // Constructor  ///////////////////////////////////////////////////////////////
    
    self.init = function(){
        self.app = app;
        
        self.bonusscore = 0;
        self.timescore = 0;
        self.update();
        
        self.hudelem = document.createElement('div');
        $(self.hudelem).addClass('HUDscore');
        self.app.hud.appendChild(self.hudelem);
        
        $(self.hudelem).css({
            'font-size' : '40px',
            'text-shadow' : '#fff 0px 0px 5px, #fff 0px 0px 10px'
        });
        
    }
    
    // Methods     ///////////////////////////////////////////////////////////////
    
    self.update = function()
    {        
        self.timescore = self.parentstate.tap;
        self.totalscore = self.timescore + self.bonusscore;
    }
    
    self.reset = function(){
        self.timescore = 0;
        self.bonusscore = 0;
    }
    
    self.draw = function(){
        // Fill with gradient
        self.hudelem.textContent = self.totalscore;
        
    }
    
    self.onDestroy = function()
    {
        self.app.hud.removeChild(self.hudelem);
    }
    
    // YEAH MAN !!! //////////////////////////////////////////////////////////////
    self.init();
}