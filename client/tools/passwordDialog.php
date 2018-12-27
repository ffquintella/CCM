<?php

/**
 * Created by PhpStorm.
 * User: felipe.quintella
 * Date: 26/12/16
 * Time: 17:49
 */
class passwordDialog extends ConsoleKit\Widgets\AbstractWidget
{
    /**
     * Writes $text and reads the user's input
     *
     * @param string $text
     * @param string $default
     * @param bool $displayDefault
     * @return string
     */
    public function ask($text, $default = '', $displayDefault = true)
    {
        if ($displayDefault && !empty($default)) {
            $defaultText = $default;
            if (strlen($defaultText) > 30) {
                $defaultText = substr($default, 0, 30) . '...';
            }
            $text .= " [$defaultText]";
        }
        $this->textWriter->write("$text ");


        if (preg_match('/^win/i', PHP_OS)) {

            $password=shell_exec('C:\Windows\system32\WindowsPowerShell\v1.0\powershell.exe -Command "$Password=Read-Host -assecurestring \"..:\" ; $PlainPassword = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto([System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($Password)) ; echo $PlainPassword;"');
            $password=explode("\n", $password); $password=$password[0];
            //echo "You have entered the following password: $pwd\n";

        } else {
            // Get current style
            $oldStyle = shell_exec('stty -g');

            shell_exec('stty -icanon -echo min 1 time 0');

            $password = '';
            while (true) {
                $char = fgetc(STDIN);

                if ($char === "\n") {
                    break;
                } else if (ord($char) === 127) {
                    if (strlen($password) > 0) {
                        fwrite(STDOUT, "\x08 \x08");
                        $password = substr($password, 0, -1);
                    }
                } else {
                    fwrite(STDOUT, "*");
                    $password .= $char;
                }
            }
            // Reset old style
            shell_exec('stty ' . $oldStyle);
            fwrite(STDOUT, "\n");
        }


        return trim($password) ?: $default;
    }
    /**
     * Writes $text (followed by the list of choices) and reads the user response.
     * Returns true if it matches $expected, false otherwise
     *
     * <code>
     * if($dialog->confirm('Are you sure?')) { ... }
     * if($dialog->confirm('Your choice?', null, array('a', 'b', 'c'))) { ... }
     * </code>
     *
     * @param string $text
     * @param string $expected
     * @param array $choices
     * @param string $default
     * @param string $errorMessage
     * @return bool
     */
    public function confirm($text, $expected = 'y', array $choices = array('Y', 'n'), $default = 'y', $errorMessage = 'Invalid choice')
    {
        $text = $text . ' [' . implode('/', $choices) . ']';
        $choices = array_map('strtolower', $choices);
        $expected = strtolower($expected);
        $default = strtolower($default);
        do {
            $input = strtolower($this->ask($text));
            if (in_array($input, $choices)) {
                return $input === $expected;
            } else if (empty($input) && !empty($default)) {
                return $default === $expected;
            }
            $this->textWriter->writeln($errorMessage);
        } while (true);
    }
}