class ClientTypeChecker {
    private timedOut: boolean = false;
    private img: HTMLImageElement = new Image();
    private timer: number | null = null;

    constructor(
        private config: brokenLinkContextDetectionData
    ) {
        this.initializeCheck();
    }

    // Initialize the client type check
    private initializeCheck(): void {
        this.setTimer();
        this.loadImage();
    }

    // Set a timeout for image loading
    private setTimer(): void {
        this.timer = window.setTimeout(() => {
            this.timedOut = true;
            this.cancelImageLoad();
            this.setExternalClient('timeout');
        }, this.config.checkTimeout);
    }

    // Load the image and handle success or failure
    private loadImage(): void {
        this.img.onload = this.handleImageLoad;
        this.img.onerror = this.handleImageError;
        this.img.src = this.config.checkUrl;
    }

    // Handle successful image load (internal client)
    private handleImageLoad = (): void => {
        if (!this.timedOut) {
            this.clearTimer();
            this.setInternalClient();
        }
    }

    // Handle image load error (external client)
    private handleImageError = (): void => {
        if (!this.timedOut) {
            this.clearTimer();
            this.setExternalClient('image error');
        }
    }

    // Clear the timer if image loads or errors before timeout
    private clearTimer(): void {
        if (this.timer) {
            clearTimeout(this.timer);
            this.timer = null;
        }
    }

    // Cancel image loading on timeout
    private cancelImageLoad(): void {
        this.img.src = ''; // Cancel image loading
    }

    // Mark as internal client
    private setInternalClient(): void {
        document.body.classList.add(this.config.successClass);
        this.log('Internal client detected (image loaded).');
    }

    // Mark as external client
    private setExternalClient(reason: string): void {
        document.body.classList.add(this.config.failedClass);
        this.log(`External client detected (${reason}).`);
    }

    // Log messages only if DevTools is open
    private log(message: string): void {
        console.log(message);
    }

    // Apply domain restrictions to domains in the domain list
    public applyDomainRestrictions(): void {
        this.config.domains.forEach(domain => {
            const elements = document.querySelectorAll(`a[href*="${domain}"]`);
            elements.forEach(element => {
                element.setAttribute("disabled", "disabled");
                element.setAttribute("data-tooltip", this.config.tooltip);
                element.addEventListener("click", (event) => {
                    event.preventDefault();
                });
            });
        });
    }
}

// Interfaces
interface brokenLinkContextDetectionData {
    isEnabled: string; // Assuming '1' or '0' as string, change to `boolean` if converted
    checkUrl: string;
    checkTimeout: number; // or number if it’s parsed as a number
    domains: string[];
    tooltip: string;
    successClass: string;
    failedClass: string;
}

declare global {
    interface Window {
        brokenLinkContextDetectionData?: brokenLinkContextDetectionData;
    }
}

// @ts-ignore Function to initialize client type checker  
export function initializeClientTypeChecker(brokenLinkContextDetectionData): void {
    
    document.addEventListener("DOMContentLoaded", () => {
        const checker = new ClientTypeChecker(brokenLinkContextDetectionData);
        checker.applyDomainRestrictions();
    });
}
// @ts-ignore
initializeClientTypeChecker(brokenLinkContextDetectionData);