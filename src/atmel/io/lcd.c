#include "lcd.h"
#include "util.h"
#include <avr/io.h>
#include <avr/pgmspace.h> 


/* Function Prototypes */
static void lcdPrint(char);
static void lcdWrite(BYTE, BYTE);
static void lcdToggleEn();
static void lcdBusyWait();

/* current cursor position */
static BYTE pos = 0;


/**
 * Initializes LCD
 */
void lcdInit() {

    LCD_CTRL_DDR = _BV(LCD_RS_PIN)|_BV(LCD_RW_PIN)|_BV(LCD_EN_PIN);

    delay_us(LCD_DELAY_START);

    lcdWrite(LCD_CMD_FUNCTION, 0);
	lcdBusyWait();

    lcdWrite(LCD_CMD_DISP, 0);
	lcdBusyWait();
	
    lcdClear();

    lcdWrite(LCD_CMD_ENTRY, 0);
	lcdBusyWait();

    /* printf */
    fdevopen((void*)lcdPrint, 0);
}

/**
 * Clears LCD screen 
 */
void lcdClear() {
    lcdWrite(LCD_CMD_CLEAR, 0);
	lcdBusyWait();
    delay_us(LCD_DELAY_SHORT);
    lcdGoTo(0,0);
}

/**
 * Moves cursor to a specified row and column starting at (0,0).
 * No checking of row and col are performed.
 * 
 * Inputs: 	row		row of lcd ( 0 <= row <= 3 )
 *			col		column of lcd ( 0 <= col <= 15 )
 */
void lcdGoTo(int row, int col) {
    switch ( row ) {
    case 0:
        lcdWrite(0x80 | (col + LCD_ADR_LINE0), 0);
        break;
    case 1:
        lcdWrite(0x80 | (col + LCD_ADR_LINE1), 0);
        break;
    case 2:
        lcdWrite(0x80 | (col + LCD_ADR_LINE2), 0);
        break;
    case 3:
        lcdWrite(0x80 | (col + LCD_ADR_LINE3), 0);
        break;
    default:
        /* default write to first row */
        lcdWrite(0x80 | (col + LCD_ADR_LINE0), 0);
    }

    pos = (row*16) + col;
}

/**
 * Prints character to LCD with support for character wrapping
 * 
 * Inputs:	db		character to print
 */
static void lcdPrint(char db) {
    if (pos==16) lcdGoTo(1, 0);
    if (pos==32) lcdGoTo(2, 0);
    if (pos==48) lcdGoTo(3, 0);
    if (pos==64) lcdGoTo(0, 0);

    lcdWrite(db, 1);
    pos++;
}

void lcdFlashPrint(const char *FlashLoc)
{
	BYTE i;
	for(i=0;(BYTE)pgm_read_byte(&FlashLoc[i]);i++) {
		lcdWrite((BYTE)pgm_read_byte(&FlashLoc[i]), 1);
	}
}


/**
 * Writes instructions to the LCD
 * 
 * Inputs: 	db		LCD instruction
 *		    	rs		for commands, rs = 0
 *			    		for sending data, rs = 1
 */
static void lcdWrite(BYTE db, BYTE rs) {
    lcdBusyWait();

	LCD_RW(0);
    LCD_RS(rs);
    LCD_DATA_DDR = 0xFF;		/* output */
   	LCD_DATA_PORT = db;
	delay_us(LCD_DELAY_EN);	    /* RS, RW Setup Time (Minimum: 60ns) */
	lcdToggleEn();
}


/**
 * Waits until busy flag of LCD is cleared
 */
static void lcdBusyWait() {
	LCD_RW(1);
    LCD_RS(0);
    LCD_DATA_DDR = 0x00;			/* input */
	delay_us(LCD_DELAY_SHORT);		/* RS, RW Setup Time (Minimum: 60ns) */
	lcdToggleEn();

    loop_until_bit_is_clear(LCD_DATA_PIN,LCD_BUSY_FLAG);
    lcdToggleEn();
}


/**
 * Toggles enable pin
 */
static void lcdToggleEn() {
	LCD_EN(1);
	delay_us(LCD_DELAY_EN);		/* E Pulse Width (Minimum: 450 ns) */
	LCD_EN(0);
}

