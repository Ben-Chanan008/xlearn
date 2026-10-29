"use strict";
class Msg {
	#btn;
	btnStyles;
    #popup;
	#defaultProperties;
	#popupDefaultProperties;
 
	constructor(msgContainer) {
		this.#defaultProperties = ["mode", "msg", "type", "useDefault"];
		this.#popupDefaultProperties = ["msg", "type"];
		this.box = `
		<div class="msg-box reveal">
			<div class="msg-card z-2">
				<div class="msg-content">
					<div class="msg-icon"></div>
					<span id="message"class="text-center"></span>
				</div>
				<button class="msg-close-btn" style="display: none"> Close </button>
			</div>
		</div>
		`;
		this.#popup = `
        <div class="msg-popup">
            <div class="msg-icon"></div>
            <div class="msg-popup-content">
                <span class="msg-popup-text"></span>
                <div class="msg-slider"></div>
			</div>
		</div>
	    `
        this.config = {
            mode: "light",
            type: "success",
            msg: "Success!! Please Wait....",
        };
		this.successIcon = `
		<svg class="msg-icons" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0,0,256,256" width="208px" height="208px">
			<g fill="#219832" id="success-circle" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none" font-size="none" text-anchor="none" style="mix-blend-mode: normal">
				<g transform="scale(9.84615,9.84615)" id="success-good">
					<path d="M13,1c-6.61719,0 -12,5.38281 -12,12c0,6.61719 5.38281,12 12,12c6.61719,0 12,-5.38281 12,-12c0,-6.61719 -5.38281,-12 -12,-12zM13,3c5.53516,0 10,4.46484 10,10c0,5.53516 -4.46484,10 -10,10c-5.53516,0 -10,-4.46484 -10,-10c0,-5.53516 4.46484,-10 10,-10zM17.1875,7.0625c-0.14844,0.02344 -0.27344,0.10156 -0.375,0.25l-4.90625,7.28125l-2.3125,-2.28125c-0.19922,-0.30078 -0.58203,-0.32422 -0.78125,-0.125l-0.90625,0.90625c-0.19922,0.30078 -0.19922,0.70703 0,0.90625l3.5,3.5c0.19922,0.10156 0.48047,0.3125 0.78125,0.3125c0.19922,0 0.51953,-0.10547 0.71875,-0.40625l6,-8.8125c0.19922,-0.30078 0.08594,-0.58203 -0.3125,-0.78125l-1,-0.71875c-0.10156,-0.05078 -0.25781,-0.05469 -0.40625,-0.03125z"></path>
				</g>
			</g>
		</svg>
		`;
        this.errorIcon = `
		<svg class="msg-icons" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 48 48" width="208px" height="208px">
			<path fill="#f44336" d="M44,24c0,11.045-8.955,20-20,20S4,35.045,4,24S12.955,4,24,4S44,12.955,44,24z"/>
			<path fill="#fff" d="M29.656,15.516l2.828,2.828l-14.14,14.14l-2.828-2.828L29.656,15.516z"/>style="display: none;
			<path fill="#fff" d="M32.484,29.656l-2.828,2.828l-14.14-14.14l2.828-2.828L32.484,29.656z"/>
		</svg>
		`;
        this.colors = {
            $msg_primary: "rgb(151, 62, 151)",
            $shadow_blue_colors: "rgba(0, 255,255,.9)",
            $shadow_red_colors: "rgba(227, 10, 27, 0.9)",
            $shadow_x_colors: "rgba(151, 62, 151, 0.6)",
            $black: "rgba(0,0,0,.8)",
        };
		
        if (typeof msgContainer === "string") {
            this.msgContainer = document.querySelector(msgContainer);
        } else if (msgContainer.constructor.name.includes("HTML"))
            this.msgContainer = msgContainer;
    }
    
	init(config) {
        this.#showMsg(config);
    }
	
    #showMsg(config) {
        const messageBox = this.msgContainer;
        messageBox.innerHTML = this.box;
        if (!Object.keys(config).length) {
            this.config = config;
        } else {
            this.config = config;
            if (Object.keys(config).length) {
                Object.keys(config).forEach((key) => {
                    if (this.#defaultProperties.indexOf(key) !== - 1) {
	                    if (!config.type) {
		                    messageBox.innerHTML = "";
		                    console.error("MsgError: No message Passed In");
	                    } else {
		                    this.msgText.innerHTML = config.msg;
							if(!config.autoClose){
								this.#btn = document.querySelector('.msg-close-btn');
								this.#btn.style.display = 'block';
 							
								if((this.#btn.constructor.name.includes('HTML'))){
									if(config.btn){
										let btnObj = config.btn
										this.btnStyles = btnObj.styles;
										
										// Uncomment: Incase unforseen errors appear
										/*if(Object.keys(btnObj.content).length){
											switch (btnObj.content.type.toLowerCase()) {
												case 'html' :
													this.#btn.innerHTML = btnObj.content.value;
													break;
												default:
													this.#btn.textContent = btnObj.content.value;
											}
										}*/
										if(btnObj.content)
											this.#btn.innerHTML = btnObj.content;
										
										if(typeof this.btnStyles === 'string')
											this.#btn.classList.add(this.btnStyles);
										else{
											let stringedStyle = this.btnStyles.toString();
											stringedStyle = stringedStyle.replace(',', ';');
											
											this.#btn.style = stringedStyle;
										}
									}
									
									this.#btn.addEventListener('click', (e) => {
										this.#hideBox(config);
									});
								}
							} else
		                        this.#timeout(config.duration ?? 5000, config);
	                    }
	
	                    this.#setIcon(config);
						
	                    switch (config.mode) {
		                    case "dark":
			                    this.msgCard.style.backgroundColor = this.colors.$black;
			                    this.msgCard.style.color = "#ffff";
			                    this.msgCard.classList.add('dark');
			                    break;
		                    default:
			                    if (this.msgCard.classList.contains('dark'))
				                    this.msgCard.classList.remove('dark');
			
			                    this.msgCard.classList.add('light');
			                    console.warn("Mode unset: Using Default");
	                    }
                    }
                });
            }
        }
    }
	
	popup(config){
		this.msgContainer.innerHTML = this.#popup;
		if(config){
			Object.keys(config).length && Object.keys(config).forEach(key => {
				if(this.#popupDefaultProperties.indexOf(key) !== -1){
					if(config.type){
						this.popupText.innerHTML = config.msg;
						this.#setIcon(config);
						this.#animate({
							item: this.popupSlider,
							keyframes: [
								{transform: 'translateX(-100%)'}
							],
							options: {
								duration: config.duration ?? 4000,
								iterations: 1
							}
						});
						setTimeout(() => {
							this.#animate({
								item: this.popupContainer,
								keyframes: [
									{transform: 'translateX(0)'},
									{transform: 'translateX(110%)'}
								],
								options: {
									duration: 300,
									iterations: 1
								}
							})
							setTimeout(() => {
								this.#redirect(config);
							}, 300)
						}, (config.duration ?? 4000) + 50)
						
						switch (config.type.trim().toLowerCase()){
							case 'success':
								this.popupContainer.style.backgroundColor = 'green';
								break;
							case 'error':
								this.popupContainer.style.backgroundColor = '#d12525';
								this.popupSlider.style.backgroundColor = '#e8b0b0';
								break;
						}
					}else
						console.error('Msg Alerts: A message type is needed');
				}
			});
		} else
			console.error('Msg Alerts: fn popup needs a config argument!');
	}
	
	#setIcon(config){
		switch (config.type.toLowerCase()) {
			case "success":
				this.msgIcon.innerHTML = this.successIcon;
				break;
			case "error":
				this.msgIcon.innerHTML = this.errorIcon;
				break;
			default:
				console.error("MsgError: No message type set");
		}
	}
	
    get msgCard() {
        return this.msgContainer.querySelector(".msg-card");
    }
    
	get msgIcon() {
        return this.msgContainer.querySelector(".msg-icon");
    }
    
	get msgText() {
        return this.msgContainer.querySelector("#message");
    }
    
	get msgBox() {
        return this.msgContainer.querySelector(".msg-box");
    }
	
	get popupContainer() {
        return document.querySelector('.msg-popup');
    }
	
	get popupText() {
        return this.popupContainer.querySelector(".msg-popup-text");
    }
	
	get popupSlider() {
        return this.popupContainer.querySelector('.msg-slider');
    }
	
	#redirect(config){
		if(config.redirect){
			location.href = config.redirect
		}
	}
	
	#hideBox(config) {
		this.msgBox.classList.remove("reveal");
		this.msgBox.classList.add("unreveal");
		setTimeout(() => {
			this.msgContainer.innerHTML = "";
			this.#redirect(config);
		}, 900);
	}
	
    #timeout(time, config) {
        setTimeout(() => {
            this.#hideBox(config);
        }, time);
    }
	
	#animate(settings = {}){
		let endingStyles = settings['keyframes'].length - 1 < 1 ? settings['keyframes'][0] : settings['keyframes'][1];
		
		settings['item'].animate(settings['keyframes'], settings['options']).finished.then(() => {
			if(Object.keys(endingStyles).length){
				Object.keys(endingStyles).forEach(style => {
					settings['item'].setAttribute('style', `${this.#hyphenate(style)} : ${endingStyles[style]}`)
				});
			}
		});
	}
	
	#hyphenate(str) {
		return str.replace(/[A-Z]/g, (match) => "-" + match.toLowerCase());
	}
	
}
