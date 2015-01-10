/**********************
        PlayState
        
        In-Game managment
***********************************/

<?php

require_once('KLine.php');
require_once('KBullets.php');
require_once('KTargets.php');
require_once('KPattern.php');
require_once('KLevel.php');
require_once('KPatternBuilder.php');

?>

function PlayState(app){
    var self = this;
    
    // Members Vars ///////////////////////////////////////////////////////////////

    self.app;
    
    self.hasToPop = false;
    
    self.line;
    
    self.bullets;
    self.scalarstm2;
    self.scalarstm1;
    self.scalarst;
    
    self.targets;
    
    self.bpm;
    self.tap;
    self.starttime;
    self.elapsed;
    
    self.pattern;
    self.patternlist;
    self.level;
    
    self.score;
    
    // Constructor  ///////////////////////////////////////////////////////////////
    
    self.init = function(){
        
        self.app = app;
        
        self.app.camera = new THREE.OrthographicCamera(
            -self.app.aspectRatio*100,      // Left
            self.app.aspectRatio*100,       // Right
            100,                      // Top
            -100,                     // Bottom
            1,                      // znear
            1000                    // zfar
        );
        self.app.camera.position.z = 100;
        
        
        self.line = new KLine(self.app, self);
        
        self.bullets = new KBullets(self.app, self);
        self.scalarstm2 = new Array(self.bullets.directions.lenght);
        self.scalarstm1 = new Array(self.bullets.directions.lenght);
        self.scalarst = new Array(self.bullets.directions.lenght);
        
        var nb_targets = 0;
        self.targets = new Array();
        /*for(var i = 0; i < self.targets.length; i++)
        {
            self.targets[i] = new KTarget(self.app, self);
        }*/
        
        self.bpm = 60. / 140.;      //beat-time in seconds
        self.tap = 0;
        self.starttime = self.app.clock.elapsedTime;
        self.elapsed = 0;
        
        self.score = new Score(self.app, self);
        
        self.pattern = new KPattern(self.app, self);
        self.patternlist = [];
        self.level = new KLevel(self.app, self);
        self.level.load();
        self.switchPattern();
    }
    
    // Methods     ///////////////////////////////////////////////////////////////
    
    self.handleEvents = function()
    {        
        self.line.handleEvents();
        self.bullets.handleEvents();
        for(var i = 0; i < self.targets.length; i++)
        {
            self.targets[i].handleEvents();
        }
    }
    
    self.update = function()
    {
        // Tap update
        self.elapsed = self.app.clock.elapsedTime - self.starttime;
        self.tap = Math.floor(self.elapsed / self.bpm);
        if(!self.pattern.update())
        {
            self.switchPattern();
        }
        
        self.line.update();
        self.bullets.update();
        for(var i = 0; i < self.targets.length; i++)
        {
            self.targets[i].update();
        }
        
        self.checkBarCollisions();
        self.checkTargetsCollisions();
        self.clearTargets();
        self.score.update();
    }
    
    self.draw = function()
    {
        self.line.draw();
        self.bullets.draw();
        for(var i = 0; i < self.targets.length; i++)
        {
            self.targets[i].draw();
        }
        self.score.draw();
    }
    
    self.onDestroy = function()
    {
        // DESTROY ALL !!!!!
    }
    
    self.switchPattern = function()
    {
        //change pattern
        var rand = Math.floor((Math.random() * self.patternlist.length));
        self.pattern = self.patternlist[rand].setPattern(self.pattern);
        self.pattern.reset();
    }
    
    self.checkBarCollisions = function()
    {
        var lineVector = [Math.cos(self.line.orientation),
                          Math.sin(self.line.orientation)];
        var lineVectortm1 = [Math.cos(self.line.orientationtm1),
                          Math.sin(self.line.orientationtm1)];
        var bulletVector, bulletVectortm1;
        var newposBullet,
            x1line = Math.cos(self.line.orientation + Math.PI/2),
            y1line = Math.sin(self.line.orientation + Math.PI/2),
            x2line = Math.cos(self.line.orientation - Math.PI/2),
            y2line = Math.sin(self.line.orientation - Math.PI/2);
        // Step 0 : Compute each bullet
        for(var i=0, is3 = 0 ; i<self.bullets.positions.length ; i+= 3, is3++)
        {
            // Step 1 : If the ball has already bounced, we don t compute it.
            if(self.bullets.hasBounced[is3] || !self.bullets.exists[is3])
                continue;
            
            //Step 2 : Check if the bullet has crossed the line            
            
            bulletVector = [self.bullets.positions[i],
                            self.bullets.positions[i+1]];
            bulletVectortm1 = [self.bullets.positionstm1[i],
                               self.bullets.positionstm1[i+1]];
            var st = lineVector[0] * bulletVector[0]
                   + lineVector[1] * bulletVector[1];
            
            self.scalarstm2[is3] = self.scalarstm1[is3];
            self.scalarstm1[is3] = self.scalarst[is3];
            self.scalarst[is3] = st;
            
            if(st*self.scalarstm2[is3] > 0 ) //st and stm1 does have the same sings.
            {
                continue;
            }
            
                    
            // Step 3 : Check the distance between the bullet and the center
            if (self.bullets.positions[i] * self.bullets.positions[i]
               + self.bullets.positions[i+1] * self.bullets.positions[i+1]
               > self.line.size * self.line.size)
            {
                // No Collision
                continue;
            }
            
            
            
            // Else : There is a cross of the line !
            
            // Step 4 : Collision ! We compute the direction of the bullet
            /*
            self.bullets.directions[is3] =
                -(-self.bullets.directions[is3] + 2*(self.line.orientation + Math.PI/2) - Math.PI / 2) + Math.PI/2;
            // C'est MEGA MOCHE, mais ça marche ! :D
            */
            
            var alpha = - self.line.orientation 
                - self.bullets.directions[is3] + Math.PI/2;
            self.bullets.directions[is3] = 2 * alpha 
                + self.bullets.directions[is3];
            
            
            // Final : We change the direction of the bullet
            self.bullets.speeds[is3] = 4;
            self.bullets.hasBounced[is3] = true;
            
        }
    }
        
    self.checkTargetsCollisions = function()
    {
        /*
        for(var i = 0; i < self.targets.length; i++)
        {
            if(self.targets[i].intersectBar(self.line))
            {
                // The target has been hit by a bullet.
                console.log("GAME OVER ?");
                console.log("position : x = " + self.targets[i].position.x + ", y = " + self.targets[i].position.y); 
                console.log("orientation = " + self.line.orientation); 
                self.targets[i].isHit = true;
            }
            
            for(var js3 = 0; js3 < self.bullets.positions.length; js3 += 3)
            {
                var p = new THREE.Vector2(
                                    self.bullets.positions[js3],
                                    self.bullets.positions[js3 + 1]);
                /*console.log("i = " + i + ", j = " + js3 / 3);
                console.log("i = " + i + " : target = " + self.targets[i] 
                            + ", length = " + self.targets.length);//
                if(self.targets[i].containsPoint(p))
                {
                    // The target has been hit by a bullet.
                    console.log("Target " + i + " hit !");
                    self.targets[i].isHit = true;
                }
            }    
        }*/
        
        
        for(var i = 0; i < self.targets.length; i++)
        {
            
            // Bullet Collision
            for(var js3 = 0; js3 < self.bullets.positions.length; js3 += 3)
            {
                if(!self.bullets.exists[js3] || !self.bullets.hasBounced[js3])
                    continue;
                    
                var p = new THREE.Vector2(
                                    self.bullets.positions[js3],
                                    self.bullets.positions[js3 + 1]);
                /*console.log("i = " + i + ", j = " + js3 / 3);
                console.log("i = " + i + " : target = " + self.targets[i] 
                            + ", length = " + self.targets.length);*/
                if(self.targets[i].containsPoint(p))
                {
                    // The target has been hit by a bullet.
                    //console.log("Target " + i + " hit !");
                    self.targets[i].isHit = true;
                }
            }    
            
            
            
            //Step 1 : We compute a Circle-Line Collision To prevent 99% of cases
            var A = [];
            A[0] = Math.cos(self.line.orientation + Math.PI/2) * self.line.size;
            A[1] = Math.sin(self.line.orientation + Math.PI/2) * self.line.size;
            
            if(!CollisionCircleSegment(A,
                                    [-A[0], -A[1]],     // B is -A
                                    [self.targets[i].position.x,
                                     self.targets[i].position.y],
                                    self.targets[i].size/2 * Math.SQRT2))
                continue;
            
            //console.log("MaybeColide");
            
            
            
            
        }
        

    }
    
    self.addTarget = function(rad, off, speed, size)
    {
        var targ = new KTarget(self.app, self);
        
        targ.position.x = Math.cos(rad) * targ.dist * self.app.aspectRatio * Math.SQRT2 
            + off * Math.cos(rad + Math.PI/2);
        
        targ.position.y = Math.sin(rad) * targ.dist * self.app.aspectRatio * Math.SQRT2
            + off * Math.sin(rad + Math.PI/2);
        
        targ.direction = -rad;
        
        targ.speed = speed;
        targ.size = size;
        
        self.targets.push(targ);
    }
    
    self.clearTargets = function()
    {
        for(var i = 0; i < self.targets.length; i++)
        {
            if(self.targets[i].isHit)
            {
                self.app.scene.remove(self.targets[i].disp_target);
                self.targets.splice(i, 1);
            }
        }
    }
    
    // YEAH MAN !!! //////////////////////////////////////////////////////////////
    self.init();
}