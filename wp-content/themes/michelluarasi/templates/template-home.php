<?php
/*
Template Name: Template Home
*/
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package michelluarasi
 */
global $current_page;
$current_page ="home";

$home_title = simple_fields_get_post_value(get_the_id(), "Home Title", true); 
$home_headline = simple_fields_get_post_value(get_the_id(), "Home Headline", true); 
$home_link_cta = simple_fields_get_post_value(get_the_id(), "Home Link CTA", true); 
$home_link_url = simple_fields_get_post_value(get_the_id(), "Home Link URL", true); 
$home_content_width = simple_fields_get_post_value(get_the_id(), "Home Content Width", true); 

get_header();?>

<div class="home-wrapper">
  <div class="home-content" style="max-width: <?php echo $home_content_width; ?>;">
      <h4 class="js-vp_reveal js-fade_in" style="color: white;"><?php echo $home_title; ?></h4>
      <h1 class="js-vp_reveal js-slide_down" style="color: white; padding-top: 0;"><?php echo $home_headline; ?></h1>
      <p class="profile__copy js-vp_reveal js-slide_up"><a class="btn btn-m btn-violet" href="<?php echo $home_link_url; ?>" title="Home Call To Action"><?php echo $home_link_cta; ?></a></p>
  </div>
  <div id="webglcontainer"></div>
</div>

<script data-cfasync="false" src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r83/three.min.js"></script>
<!-- <?php // <script data-cfasync="false" src="<?php bloginfo('template_url'); ?>/js/webgl/OrbitControls.min.js"></script>-->
<script data-cfasync="false" src="<?php bloginfo('template_url'); ?>/js/webgl/DeviceOrientationControls.min.js"></script>
<script data-cfasync="false" src="<?php bloginfo('template_url'); ?>/js/webgl/Maf.min.js"></script>
<script data-cfasync="false" src="<?php bloginfo('template_url'); ?>/js/webgl/THREE.FBOHelper.js"></script>
<script data-cfasync="false" src="<?php bloginfo('template_url'); ?>/js/webgl/isMobile.min.js"></script>
<script data-cfasync="false" src="<?php bloginfo('template_url'); ?>/js/webgl/linesOnSphere.js"></script>



    <script data-cfasync="false">
      // var stats = new Stats();  // FPS STATS
      // stats.showPanel( 0 ); // 0: fps, 1: ms, 2: mb, 3+: custom  // FPS STATS
      document.body.appendChild( stats.dom );

      function animate() {

      requestAnimationFrame( animate );
         // stats.begin();  // FPS STATS

        /*  controls.update();  ORBIT CONTROLS*/

          simulationShader.uniforms.value = .0001 * performance.now();
          simulationShader.uniforms.positions.value = targets[ targetPos ].texture;
          targetPos = 1 - targetPos;
          renderer.render( rtScene, rtCamera, targets[ targetPos ] );

          renderer.autoClear = false;

          orthoQuad.visible = true; orthoMesh.visible = false;
          clearShader.uniforms.texture.value = textureFBO[ targetTexture ].texture;
          targetTexture = 1 - targetTexture;
          renderer.render( orthoScene, orthoCamera, textureFBO[ targetTexture ] );
          textureShader.uniforms.positions.value = targets[ targetPos ].texture;
          orthoQuad.visible = false; orthoMesh.visible = true;
          renderer.render( orthoScene, orthoCamera, textureFBO[ targetTexture ] );
          //sphere.material.map = textureFBO[ targetTexture ];
          renderer.autoClear = true;

          mesh.material.uniforms.positions.value = targets[ targetPos ].texture;

          renderer.render( scene, camera );
          helper.update();
  
          // stats.end(); // FPS STATS
      }
      animate();

    </script>



<script data-cfasync="false" type="x-shader/x-vertex" id="clear-vs">
precision highp float;

attribute vec3 position;
attribute vec2 uv;

uniform mat4 modelViewMatrix;
uniform mat4 projectionMatrix;

uniform sampler2D texture;

varying vec2 vUv;

void main() {

  vUv = uv;
  gl_Position = projectionMatrix * modelViewMatrix * vec4( position, 1. );

}

</script>

<script data-cfasync="false" type="x-shader/x-fragment" id="clear-fs">
precision highp float;

uniform sampler2D texture;

varying vec2 vUv;

void main() {

  vec2 uv = vUv;
  uv.y = 1. - uv.y;
  vec4 c = texture2D( texture, uv ) - .01;
  gl_FragColor = c;

}

</script>
<script data-cfasync="false" type="x-shader/x-vertex" id="texture-vs">
precision highp float;

attribute vec3 position;

uniform mat4 modelMatrix;
uniform mat4 modelViewMatrix;
uniform mat4 projectionMatrix;

uniform float streakType;
uniform sampler2D positions;
uniform vec2 dimensions;

#define M_PI 3.1415926535897932384626433832795

float azimuth( vec3 vector ) {

  return atan( vector.z, - 1.0 * vector.x );

}

float inclination( vec3 vector ) {

  return atan( - vector.y, sqrt( ( vector.x * vector.x ) + ( vector.z * vector.z ) ) );

}

void main() {

  vec2 uv = position.xy;
  vec4 c = texture2D( positions, uv );
  vec3 p = 500. * c.xyz;

  vec2 uv2 = vec2( azimuth( p ) / 2. / M_PI + 0.5, inclination( p ) / M_PI + 0.5 );

  float x = uv2.x - .5;
  float y = uv2.y - .5;

  p.xyz = vec3( vec2( x, y ) * dimensions, 0. );

  gl_PointSize = mix( 1., ( c.a / 100. ) * dimensions.x / 500., streakType );
  gl_Position = projectionMatrix * modelViewMatrix * vec4( p, 1. );

}

</script>

<script data-cfasync="false" type="x-shader/x-fragment" id="texture-fs">
precision highp float;

void main() {

  vec2 uv = gl_PointCoord;
  float d = length( uv - .5 );
  if( d > .5 ) discard;

  gl_FragColor = vec4( 1. );

}

</script>

<script data-cfasync="false" type="x-shader/x-vertex" id="particle-vs">
precision highp float;

attribute vec3 position;

uniform mat4 modelMatrix;
uniform mat4 modelViewMatrix;
uniform mat4 projectionMatrix;

uniform sampler2D positions;

void main() {

  vec2 uv = position.xy;
  vec3 p = 100. * texture2D( positions, uv ).xyz;
  vec4 mvPosition = modelViewMatrix * vec4( p, 1. );
  gl_PointSize = 1. * ( 300.0 / -mvPosition.z );
  gl_Position = projectionMatrix * mvPosition;

}

</script>

<script data-cfasync="false" type="x-shader/x-fragment" id="particle-fs">
precision highp float;

void main() {

  gl_FragColor = vec4( 1., 0., 1., 1. );
}

</script>

<script data-cfasync="false" type="x-shader/x-vertex" id="simulation-vs">
precision highp float;

attribute vec3 position;
attribute vec2 uv;

uniform mat4 modelViewMatrix;
uniform mat4 projectionMatrix;

varying vec2 vUv;

void main() {

  vUv = uv;
  gl_Position = projectionMatrix * modelViewMatrix * vec4( position, 1. );

}

</script>

<script data-cfasync="false" type="x-shader/x-fragment" id="simulation-fs">
precision highp float;

uniform sampler2D positions;
uniform sampler2D original;
uniform float time;

varying vec2 vUv;

vec3 mod289(vec3 x) {
  return x - floor(x * (1.0 / 289.0)) * 289.0;
}
vec4 mod289(vec4 x) {
  return x - floor(x * (1.0 / 289.0)) * 289.0;
}
vec4 permute(vec4 x) {
  return mod289(((x*34.0)+1.0)*x);
}
vec4 taylorInvSqrt(vec4 r){
  return 1.79284291400159 - 0.85373472095314 * r;
}

vec4 snoiseD(vec3 v) { //returns vec4(value, dx, dy, dz)
  const vec2  C = vec2(1.0/6.0, 1.0/3.0) ;
  const vec4  D = vec4(0.0, 0.5, 1.0, 2.0);
  vec3 i  = floor(v + dot(v, C.yyy) );
  vec3 x0 =   v - i + dot(i, C.xxx) ;
  vec3 g = step(x0.yzx, x0.xyz);
  vec3 l = 1.0 - g;
  vec3 i1 = min( g.xyz, l.zxy );
  vec3 i2 = max( g.xyz, l.zxy );
  vec3 x1 = x0 - i1 + C.xxx;
  vec3 x2 = x0 - i2 + C.yyy;
  vec3 x3 = x0 - D.yyy;
  i = mod289(i);
  vec4 p = permute( permute( permute(
             i.z + vec4(0.0, i1.z, i2.z, 1.0 ))
           + i.y + vec4(0.0, i1.y, i2.y, 1.0 ))
           + i.x + vec4(0.0, i1.x, i2.x, 1.0 ));
  float n_ = 0.142857142857; // 1.0/7.0
  vec3  ns = n_ * D.wyz - D.xzx;
  vec4 j = p - 49.0 * floor(p * ns.z * ns.z);
  vec4 x_ = floor(j * ns.z);
  vec4 y_ = floor(j - 7.0 * x_ );
  vec4 x = x_ *ns.x + ns.yyyy;
  vec4 y = y_ *ns.x + ns.yyyy;
  vec4 h = 1.0 - abs(x) - abs(y);
  vec4 b0 = vec4( x.xy, y.xy );
  vec4 b1 = vec4( x.zw, y.zw );
  vec4 s0 = floor(b0)*2.0 + 1.0;
  vec4 s1 = floor(b1)*2.0 + 1.0;
  vec4 sh = -step(h, vec4(0.0));
  vec4 a0 = b0.xzyw + s0.xzyw*sh.xxyy ;
  vec4 a1 = b1.xzyw + s1.xzyw*sh.zzww ;
  vec3 p0 = vec3(a0.xy,h.x);
  vec3 p1 = vec3(a0.zw,h.y);
  vec3 p2 = vec3(a1.xy,h.z);
  vec3 p3 = vec3(a1.zw,h.w);
  vec4 norm = taylorInvSqrt(vec4(dot(p0,p0), dot(p1,p1), dot(p2, p2), dot(p3,p3)));
  p0 *= norm.x;
  p1 *= norm.y;
  p2 *= norm.z;
  p3 *= norm.w;
  vec4 values = vec4( dot(p0,x0), dot(p1,x1), dot(p2,x2), dot(p3,x3) ); //value of contributions from each corner (extrapolate the gradient)
  vec4 m = max(0.5 - vec4(dot(x0,x0), dot(x1,x1), dot(x2,x2), dot(x3,x3)), 0.0); //kernel function from each corner
  vec4 m2 = m * m;
  vec4 m3 = m * m * m;
  vec4 temp = -6.0 * m2 * values;
  float dx = temp[0] * x0.x + temp[1] * x1.x + temp[2] * x2.x + temp[3] * x3.x + m3[0] * p0.x + m3[1] * p1.x + m3[2] * p2.x + m3[3] * p3.x;
  float dy = temp[0] * x0.y + temp[1] * x1.y + temp[2] * x2.y + temp[3] * x3.y + m3[0] * p0.y + m3[1] * p1.y + m3[2] * p2.y + m3[3] * p3.y;
  float dz = temp[0] * x0.z + temp[1] * x1.z + temp[2] * x2.z + temp[3] * x3.z + m3[0] * p0.z + m3[1] * p1.z + m3[2] * p2.z + m3[3] * p3.z;
  return vec4(dot(m3, values), dx, dy, dz) * 42.0;
}

vec3 curlNoise (vec3 p) {
    vec3 xNoisePotentialDerivatives = snoiseD( p ).yzw; //yzw are the xyz derivatives
    vec3 yNoisePotentialDerivatives = snoiseD(vec3( p.y - 19.1 , p.z + 33.4 , p.x + 47.2 )).yzw;
    vec3 zNoisePotentialDerivatives = snoiseD(vec3( p.z + 74.2 , p.x - 124.5 , p.y + 99.4 )).yzw;
    vec3 noiseVelocity = vec3(
        zNoisePotentialDerivatives.y - yNoisePotentialDerivatives.z,
        xNoisePotentialDerivatives.z - zNoisePotentialDerivatives.x,
        yNoisePotentialDerivatives.x - xNoisePotentialDerivatives.y
    );
    return noiseVelocity;
}

float rand(vec2 co){
    return fract(sin(dot(co.xy ,vec2(12.9898,78.233))) * 43758.5453);
}

void main() {

  vec4 c = texture2D( positions, vUv );
  c.xyz += .00025 * curlNoise( 2. * c.xyz + vec3( time, 0., 0. ) );
  c.xyz = normalize( c.xyz );
  c.a += .5;
  if( c.a > 100. ) {
    c = .5 * vec4( .5 - rand( c.xy ), .5 - rand( c.zy ), .5 - rand( c.xz ), 0. );
    c += texture2D( original, vec2( vUv.x, 0. ) );
    c.xyz = normalize( c.xyz );
    c.a = 0.;
  }
  gl_FragColor = c;
}

</script>



<?php get_footer(); ?>