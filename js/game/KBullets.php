/**********************
        KBullets
        
        Bullet Manager of the level
***********************************/


function KBullets(app, parentstate){
    var self = this;
    
    // Members Vars ///////////////////////////////////////////////////////////////

    self.app;
    self.parentstate;
    
    // Logic
    
    self.positions;
    self.positionstm1;
    self.colors;
    self.directions;
    self.speeds;
    self.hasBounced;
    self.dist;
    self.exists;
    self.cleardist;
    
    self.nb;
    
    
    // Display
    
    self.particles;
    
    // Constructor  ///////////////////////////////////////////////////////////////
    
    self.init = function(){
        self.app = app;
        self.parentstate = parentstate;
        
        self.nb = 256;
        
        var geombuf = new THREE.BufferGeometry();
        self.positions = new Float32Array(self.nb*3);
        self.positionstm1 = new Float32Array(self.nb*3);     // pos at t-1
        self.colors = new Float32Array(self.nb*3);
        self.directions = [];
        self.speeds = [];
        self.hasBounced = [];
        self.exists = [];
        
        self.dist = Math.sqrt(10000 + 10000*self.app.aspectRatio*self.app.aspectRatio);
        self.cleardist = self.dist + 10;
        
        var rad;
        var off;
        
        for ( var i = 0, is3 = 0 ; i < self.nb*3; i += 3, is3++ )
        {
            
            /*rad = Math.random()*Math.PI*2;
            //rad = Math.PI / 2;
            off = Math.random()*40-20;
            //off = 0;
            self.positions[i] = Math.cos(rad) * self.dist * self.app.aspectRatio * Math.SQRT2;
            self.positions[i+1] = Math.sin(rad) * self.dist * self.app.aspectRatio * Math.SQRT2;
            self.positions[i+2] = 0;
            
            self.directions[is3] = -rad;
            self.speeds[is3] = 1;
            self.hasBounced[is3] = false;
            self.positions[i] += off * Math.cos(rad + Math.PI/2);
            self.positions[i+1] += off * Math.sin(rad + Math.PI/2);*/
            self.exists[is3] = false;
        }
        
        var texture = THREE.ImageUtils.loadTexture( "img/spark2.png" );
        
        var material = new THREE.PointCloudMaterial(
            {size:16,
             sizeAttenuation:false,
             map:texture,
             transparent:true,
             vertexColors: THREE.VertexColors
            }
        );
        material.color.set(0xFFFFFF);
        
        self.particles = new THREE.PointCloud(geombuf, material);
        self.particles.sortParticles = true;
        self.app.scene.add(self.particles);
    }
    
    // Methods     ///////////////////////////////////////////////////////////////
    
    self.handleEvents = function()
    {
        // Nothing ?
    }
    
    self.update = function()
    {
        for ( var i = 0, is3 = 0 ; i < self.nb*3; i += 3, is3++ )
        {
            self.positionstm1[i] = self.positions[i];
            self.positionstm1[i+1] = self.positions[i+1];
            
            self.positions[i] -= Math.cos(self.directions[is3]) * self.speeds[is3];
            self.positions[i+1] += Math.sin(self.directions[is3]) * self.speeds[is3];
            
        }
        self.clearbullets();
        self.particles.geometry.addAttribute( 'position', new THREE.BufferAttribute( self.positions, 3 ) );
        self.particles.geometry.addAttribute( 'color', new THREE.BufferAttribute( self.colors, 3 ) );
    }
    
    self.draw = function()
    {

    }
    
    self.onDestroy = function(){
        self.app.scene.remove(self.particles);
    }
    
    self.addBullet = function(rad, off, speed)
    {
        //step 1 : find a position within the table where the bullet is free
        var i ;
        for(i = 0; i<self.nb; ++i){
            if(!self.exists[i]) break;
        }
        
        // i is the number of the free index
        
        self.positions[i*3] = Math.cos(rad) * self.dist;
        self.positions[i*3+1] = Math.sin(rad) * self.dist;
        self.positions[i*3+2] = 0;
        
        self.colors[i*3] = 0x41 / 0xff;
        self.colors[i*3+1] = 0xDA /0xff;
        self.colors[i*3+2] = 0xE9 /0xff;

        self.directions[i] = -rad;
        self.speeds[i] = speed;
        self.hasBounced[i] = false;
        self.positions[i*3] += off * Math.cos(rad + Math.PI/2);
        self.positions[i*3+1] += off * Math.sin(rad + Math.PI/2);
        self.exists[i] = true;
    }
    
    self.clearbullets = function(){
        for ( var i = 0, is3 = 0 ; i < self.nb*3; i += 3, is3++ )
        {
            if(!self.exists[is3]) continue;
            
            if(self.positions[i] * self.positions[i] + self.positions[i+1] * self.positions[i+1] > self.cleardist * self.cleardist
            )
            {
                self.exists[is3] = false;
                //console.log("clear bullet " + is3);
            }
        }
    }
    
    // YEAH MAN !!! //////////////////////////////////////////////////////////////
    self.init();
}