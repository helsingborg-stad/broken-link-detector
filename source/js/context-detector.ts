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
        const storedResult = sessionStorage.getItem('brokenLinkClientType');

        if (storedResult) {
            this.applyStoredResult(storedResult);
        } else {
            this.setTimer();
            this.loadImage();
        }
    }

    // Apply a stored result
    private applyStoredResult(result: string): void {
        if (result === 'internal') {
            this.setInternalClient();
        } else if (result === 'external') {
            this.setExternalClient();
        }
    }

    // Set a timeout for image loading
    private setTimer(): void {
        this.timer = window.setTimeout(() => {
            this.timedOut = true;
            this.cancelImageLoad();
            this.setExternalClient();
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
            this.setExternalClient();
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
        document.dispatchEvent(new CustomEvent('brokenLinkContextDetectionInternal'));
        document.body.classList.add(this.config.successClass);
        sessionStorage.setItem('brokenLinkClientType', 'internal');
    }

    // Mark as external client
    private setExternalClient(): void {
        // Execute custom event
        document.dispatchEvent(new CustomEvent('brokenLinkContextDetectionExternal'));
        document.body.classList.add(this.config.failedClass);
        this.applyDomainRestrictions();
        sessionStorage.setItem('brokenLinkClientType', 'external');
    }

    // Apply domain restrictions to domains in the domain list
    private applyDomainRestrictions(): void {
        this.config.domains.forEach(domain => {
            const elements = document.querySelectorAll(`a[href*="${domain}"]`);
            elements.forEach(element => {
                this.addUnavailableClass(element);
                this.preventDefaultOnClick(element);
    
                if (this.config.isToolTipActive) {
                    this.addTooltip(element);
                }
                
                if (this.config.isModalActive) {
                    this.addModalAttributes(element);
                }
            });
        });
    
        this.reindexModals();
    }

    private addUnavailableClass(element: Element): void {
        element.classList.add('broken-link-detector-link-is-unavailable');
    }

    private preventDefaultOnClick(element: Element): void {
        element.addEventListener("click", (event) => {
            event.preventDefault();
        });
    }

    private addTooltip(element: Element): void {
        element.setAttribute("data-tooltip", this.config.tooltip);
    }

    private addModalAttributes(element: Element): void {
        element.setAttribute("data-open", "modal-broken-link");
        element.addEventListener("click", () => {
            const modalButton = document.getElementById("modal-broken-link-button");
            if (modalButton) {
                modalButton.setAttribute("href", element.getAttribute("href") || "#linknotfound");
            }
        });
    }
    
    private reindexModals(): void {
        document.dispatchEvent(new CustomEvent('reindexModals'));
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
    isToolTipActive: boolean;
    isModalActive: boolean;
}

declare global {
    interface Window {
        brokenLinkContextDetectionData?: brokenLinkContextDetectionData;
    }
}

// @ts-ignore Function to initialize client type checker  
export function initializeClientTypeChecker(brokenLinkContextDetectionData): void {
    document.addEventListener("DOMContentLoaded", () => {
        new ClientTypeChecker(brokenLinkContextDetectionData);
    });
}
// @ts-ignore
initializeClientTypeChecker(brokenLinkContextDetectionData);