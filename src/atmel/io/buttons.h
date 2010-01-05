#ifndef _BUTTONS_H_
#define _BUTTONS_H_

#include "global.h"

#define BUTTONS_TIMER_CNT	18

/* Push Buttons */
#define BUTTONS(byte, val)  {if (val==1) { \
								byte |= _BV(BUTTON_LEFT)|_BV(BUTTON_RIGHT)|_BV(BUTTON_UP)|_BV(BUTTON_DOWN); \
                             } else { \
                             	byte &= ~_BV(BUTTON_LEFT)|~_BV(BUTTON_RIGHT)|~_BV(BUTTON_UP)|~_BV(BUTTON_DOWN); \
                             };}

/* Button Functions */
void buttonInit();

#endif /* _BUTTONS_H_ */
