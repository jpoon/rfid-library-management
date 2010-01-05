#include "buttons.h"
#include "lcd.h"
#include "lcdMenu.h"
#include <avr/io.h>
#include <avr/interrupt.h>

volatile BOOL buttonEnter;

/* Function Prototypes */
static void timer0Init( void );

/**
 * Initializes buttons
 */
void buttonInit( void ) {
	BUTTONS(BUTTONS_DDR,0);
	BUTTONS(BUTTONS_PORT,1);

	buttonEnter = FALSE;		

    timer0Init();
}

/**
 * Initialize Timer 0 for overflow interrupt
 */
static void timer0Init( void ) {
   	/* reset timer */
   	TCNT0 = 0;
	/* prescaler = 256 */
	TCCR0 = _BV(CS02);
	/* enable timer overflow interrupt */
   	TIMSK |= _BV(TOIE0);
}


/**
 * Timer 0 Overflow ISR 
 */
ISR(TIMER0_OVF_vect, ISR_NOBLOCK) {
    static BYTE btnState, oldState, newState, count;

    newState = BUTTONS_PIN;
    if ( newState == oldState ) {
        count++;
    } else {
        count = 0;             
    }
    oldState = newState;

    if (count == BUTTONS_TIMER_CNT) {
        btnState = newState; 
        count = 0;
		
		buttonEnter = FALSE;

		if (btnState & _BV(BUTTON_LEFT)) menuBack();
		if (btnState & _BV(BUTTON_RIGHT)) menuExecute();
		if (btnState & _BV(BUTTON_UP)) menuBuild(1);
		if (btnState & _BV(BUTTON_DOWN)) menuBuild(-1);
    }
}
