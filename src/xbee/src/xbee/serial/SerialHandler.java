import gnu.io.CommPort;
import gnu.io.CommPortIdentifier;
import gnu.io.SerialPort;

import java.io.InputStream;
import java.io.OutputStream;

public class SerialHandler
{
	
    public SerialHandler()
    {
        super();
    }
    
    void connect ( String portName ) throws Exception
    {
        CommPortIdentifier portIdentifier = CommPortIdentifier.getPortIdentifier(portName);
        if ( portIdentifier.isCurrentlyOwned() )
        {
            System.out.println("Error: Port is currently in use");
        }
        else
        {
            CommPort commPort = portIdentifier.open(this.getClass().getName(),2000);
            
            if ( commPort instanceof SerialPort )
            {
                SerialPort serialPort = (SerialPort) commPort;
                serialPort.setSerialPortParams(9600,SerialPort.DATABITS_8,SerialPort.STOPBITS_1,SerialPort.PARITY_NONE);
                
                InputStream in = serialPort.getInputStream();
                OutputStream out = serialPort.getOutputStream();
                
                Thread serialWriter = new Thread(new SerialWriter(out));
                serialWriter.setName("SerialWriter");
                serialWriter.start();
                
                Thread serialReader = new Thread(new SerialReader(in));
                serialReader.setName("SerialReader");
                serialReader.start();
                
                Thread serialPHPWriter = new Thread(new SerialPHPWriter(out));
                serialPHPWriter.setName("SerialPHPWriter");
                serialPHPWriter.start();
                

            }
            else
            {
                System.out.println("Error: Only serial ports are handled by this example.");
            }
        }     
    }

    
    public static void main ( String[] args )
    {
    
        try
        {
        	String port = "COM" + args[0];
            (new SerialHandler()).connect(port);
        }
        catch ( Exception e )
        {
            System.out.println("Please enter the COM port you are using as a command line argument.");
            System.out.println("Example: java SerialHander 6");
            System.out.println("This opens COM6.");
            System.out.print("If you are getting this message, you either did not enter a COM port number");
            System.out.println(	" or you have specified a port that does not have a device connected.");
        }
    }
}
