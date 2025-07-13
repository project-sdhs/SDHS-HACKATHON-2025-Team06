// index.js

document.addEventListener('DOMContentLoaded', () => {
    const sections = document.querySelectorAll('.page');
    const dotsWrapper = document.querySelector('.dots-wrapper');
    let currentSectionIndex = 0;
    let isScrolling = false; // 스크롤 중복 방지 플래그

    // 스크롤 애니메이션 지속 시간 (밀리초) - CSS의 scroll-behavior: smooth와 유사하게 맞추거나 조금 길게 설정
    const SCROLL_DURATION = 800; // 0.8초로 설정하여 더 부드럽게 느껴지도록


    const dots = document.querySelectorAll('.dot');

    function updateDots() {

        dots.forEach((dot, index) => {
            if (index === currentSectionIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }

    function scrollToSection(index) {
        if (index >= 0 && index < sections.length && !isScrolling) {
            isScrolling = true;
            sections[index].scrollIntoView({ behavior: 'smooth' });
            currentSectionIndex = index;
            updateDots();

            // 스크롤 완료 후 플래그 해제
            // SCROLL_DURATION 값에 따라 조정하여 스크롤 애니메이션이 끝난 후 다음 스크롤을 허용
            setTimeout(() => {
                isScrolling = false;
            }, SCROLL_DURATION);
        }
    }

    // 마우스 휠 이벤트 리스너
    window.addEventListener('wheel', (event) => {
        if (isScrolling) {
            return;
        }

        if (event.deltaY > 0) { // 아래로 스크롤
            scrollToSection(currentSectionIndex + 1);
        } else { // 위로 스크롤
            scrollToSection(currentSectionIndex - 1);
        }
        dots.forEach(dot => {
            dot.classList.remove('op1')
            dot.classList.add('op05')
        });
        dots[currentSectionIndex].classList.add('op1');

    });

    // 키보드 방향키 이벤트 리스너
    window.addEventListener('keydown', (event) => {
        if (isScrolling) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            scrollToSection(currentSectionIndex + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            scrollToSection(currentSectionIndex - 1);
        }
    });

    // 페이지 로드 시 첫 번째 섹션으로 이동 및 점 활성화
    scrollToSection(0);
});

